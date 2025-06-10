<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Gallery;
use App\Models\Image;
use Exception;
use Illuminate\Support\Str;
use Log; // Importação correta para a facade Log

class ProcessImageWithGd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // A propriedade agora é 'tempRelativePath' para refletir EXATAMENTE o que é passado pelo Controller.
    protected string $tempRelativePath;
    protected int $galleryId;
    protected string $originalFileName;
    protected ?string $watermarkFile;

    // O construtor agora recebe o caminho relativo COMPLETO diretamente.
    public function __construct(string $tempRelativePath, int $galleryId, string $originalFileName, ?string $watermarkFile = null)
    {
        $this->tempRelativePath = $tempRelativePath;
        $this->galleryId = $galleryId;
        $this->originalFileName = $originalFileName;
        $this->watermarkFile = $watermarkFile;
    }

    public function handle(): void
    {
        $diskLocal = Storage::disk('local');   // Disco 'local' para o arquivo temporário
        $diskPublic = Storage::disk('public'); // Disco 'public' para o destino final dos arquivos processados

        try {
            // Verifica se o arquivo temporário existe no disco 'local' usando o caminho relativo recebido
            if (!$diskLocal->exists($this->tempRelativePath)) {
                // Se o arquivo não existir, o job simplesmente retorna.
                Log::warning("ProcessImageWithGd Job: Arquivo temporário não encontrado no disco 'local' para o caminho: {$this->tempRelativePath}. Pulando.");
                return;
            }

            $gallery = Gallery::find($this->galleryId);
            if (!$gallery) {
                // Se a galeria não for encontrada, deleta o arquivo temporário e retorna.
                Log::error("ProcessImageWithGd Job: Galeria não encontrada (ID: {$this->galleryId}). Deletando arquivo temporário: {$this->tempRelativePath}.");
                $diskLocal->delete($this->tempRelativePath);
                return;
            }

            // Lê o conteúdo da imagem do caminho relativo no disco 'local'
            $imageContent = $diskLocal->get($this->tempRelativePath);

            // --- Gerar nomes de arquivo únicos e slugs para armazenamento ---
            $uuid = Str::uuid();
            $originalSlug = Str::slug(pathinfo($this->originalFileName, PATHINFO_FILENAME));
            $extension = pathinfo($this->originalFileName, PATHINFO_EXTENSION); // Usar a extensão do nome original

            $finalOriginalName = $uuid . '_' . $originalSlug . '.' . $extension;
            $finalThumbName = $uuid . '_' . $originalSlug . '_thumb.' . $extension;
            $finalWatermarkedName = $uuid . '_' . $originalSlug . '_wm.' . $extension;

            $watermarkApplied = false;
            $watermarkedStoragePath = null;

            // --- Processamento da Imagem Original/Principal com GD ---
            $imgOriginal = imagecreatefromstring($imageContent);
            if (!$imgOriginal) {
                throw new Exception("Não foi possível criar imagem a partir do string para o original. Formato inválido?");
            }

            $originalWidth = imagesx($imgOriginal);
            $originalHeight = imagesy($imgOriginal);

            $maxWidth = 1920;
            $maxHeight = 1080;
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;

            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
                $newWidth = (int)($originalWidth * $ratio);
                $newHeight = (int)($originalHeight * $ratio);
            }

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
            if (in_array(strtolower($extension), ['png'])) {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            imagecopyresampled($resizedImage, $imgOriginal, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            imagedestroy($imgOriginal);

            // --- SALVAR A IMAGEM ORIGINAL (SEM MARCA D'ÁGUA) ---
            $originalStoragePath = 'galleries/' . $gallery->id . '/' . $finalOriginalName;
            ob_start();
            imagejpeg($resizedImage, null, 80);
            $diskPublic->put($originalStoragePath, ob_get_clean()); // Salva o caminho RELATIVO no disco 'public'


            // --- Processamento e Aplicação da Marca D'água ---
            if ($this->watermarkFile && !empty($this->watermarkFile)) {
                $watermarkDiskPath = 'watermarks/' . $this->watermarkFile;
                if ($diskPublic->exists($watermarkDiskPath)) {
                    $watermarkFileContent = $diskPublic->get($watermarkDiskPath);
                    $watermark = imagecreatefromstring($watermarkFileContent);
                    if ($watermark) {
                        $watermarkedImage = imagecreatetruecolor($newWidth, $newHeight);
                        imagecopy($watermarkedImage, $resizedImage, 0, 0, 0, 0, $newWidth, $newHeight);

                        $watermarkWidth = imagesx($watermark);
                        $watermarkHeight = imagesy($watermark);

                        $targetWatermarkWidth = min($newWidth * 0.20, 200);
                        $watermarkRatio = ($watermarkWidth > 0) ? $targetWatermarkWidth / $watermarkWidth : 1;
                        $scaledWatermarkWidth = (int)($watermarkWidth * $watermarkRatio);
                        $scaledWatermarkHeight = (int)($watermarkHeight * $watermarkRatio);

                        $scaledWatermark = imagecreatetruecolor($scaledWatermarkWidth, $scaledWatermarkHeight);
                        imagealphablending($scaledWatermark, false);
                        imagesavealpha($scaledWatermark, true);
                        imagecopyresampled($scaledWatermark, $watermark, 0, 0, 0, 0, $scaledWatermarkWidth, $scaledWatermarkHeight, $watermarkWidth, $watermarkHeight);
                        imagedestroy($watermark);

                        $padding = 10;
                        $destX = $newWidth - $scaledWatermarkWidth - $padding;
                        $destY = $newHeight - $scaledWatermarkHeight - $padding;

                        imagecopy($watermarkedImage, $scaledWatermark, $destX, $destY, 0, 0, $scaledWatermarkWidth, $scaledWatermarkHeight);
                        imagedestroy($scaledWatermark);

                        $watermarkedStoragePath = 'galleries/' . $gallery->id . '/watermarked/' . $finalWatermarkedName;
                        ob_start();
                        imagejpeg($watermarkedImage, null, 80);
                        $diskPublic->put($watermarkedStoragePath, ob_get_clean());
                        imagedestroy($watermarkedImage);
                        $watermarkApplied = true;
                    }
                } else {
                    Log::warning("ProcessImageWithGd Job: Marca d'água não encontrada em '{$watermarkDiskPath}'. Por favor, verifique se o arquivo está em 'storage/app/public/watermarks/'.");
                }
            }


            // --- Gerar Thumbnail ---
            $thumbWidth = 300;
            $thumbHeight = 200; // Altura fixa para thumbnails

            $srcRatio = $newWidth / $newHeight;
            $targetRatio = $thumbWidth / $thumbHeight;

            $srcX = 0;
            $srcY = 0;
            $srcW = $newWidth;
            $srcH = $newHeight;

            if ($srcRatio > $targetRatio) { // Imagem original mais larga que o thumbnail
                $srcW = $newHeight * $targetRatio;
                $srcX = ($newWidth - $srcW) / 2;
            } else { // Imagem original mais alta ou igual que o thumbnail
                $srcH = $newWidth / $targetRatio;
                $srcY = ($newHeight - $srcH) / 2;
            }

            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
            if (in_array(strtolower($extension), ['png'])) {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
                imagefilledrectangle($thumb, 0, 0, $thumbWidth, $thumbHeight, $transparent);
            }
            imagecopyresampled($thumb, $resizedImage, 0, 0, (int)$srcX, (int)$srcY, $thumbWidth, $thumbHeight, (int)$srcW, (int)$srcH);
            imagedestroy($resizedImage); // Libera memória da imagem redimensionada

            $thumbStoragePath = 'galleries/' . $gallery->id . '/thumbs/' . $finalThumbName; // Criando pasta 'thumbs'
            ob_start();
            imagejpeg($thumb, null, 80);
            $diskPublic->put($thumbStoragePath, ob_get_clean()); // Salva o caminho RELATIVO
            imagedestroy($thumb);


            // --- Salvar informações da imagem no banco de dados ---
            $image = new Image();
            $image->gallery_id = $gallery->id;
            $image->original_file_name = $this->originalFileName;
            $image->path_original = $originalStoragePath;
            $image->path_thumb = $thumbStoragePath;
            $image->watermark_applied = $watermarkApplied; // Define se a marca d'água foi aplicada
            $image->metadata = [
                'original_width' => $originalWidth,
                'original_height' => $originalHeight,
                'watermarked_path' => $watermarkedStoragePath,
                'watermark_file_used' => $this->watermarkFile,
                'final_width' => $newWidth,
                'final_height' => $newHeight,
                'file_size' => $diskPublic->size($originalStoragePath),
                'mime_type' => 'image/jpeg',
            ];
            $image->save();

        } catch (Exception $e) {
            Log::error("ProcessImageWithGd Job: Falha ao processar imagem '{$this->originalFileName}' para galeria {$this->galleryId}: " . $e->getMessage() . " na linha " . $e->getLine() . " em " . $e->getFile());
            $this->fail($e); // Marca o Job como falho explicitamente
        } finally {
            // Deleta o arquivo temporário do disco 'local' no final, independente do sucesso ou falha
            try {
                if ($diskLocal->exists($this->tempRelativePath)) {
                    $diskLocal->delete($this->tempRelativePath);
                    Log::info("ProcessImageWithGd Job: Arquivo temporário '{$this->tempRelativePath}' deletado do disco local.");
                }
            } catch (Exception $e) {
                Log::error("ProcessImageWithGd Job: Erro ao deletar arquivo temporário '{$this->tempRelativePath}': " . $e->getMessage());
            }
        }
    }
}
