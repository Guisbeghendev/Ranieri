// resources/js/bootstrap.js

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// --- ESSA É A PARTE CRÍTICA QUE FOI ADICIONADA/CORRIGIDA ---
// Pega o token CSRF da meta tag no cabeçalho HTML (do seu app.blade.php)
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    // Configura o cabeçalho X-CSRF-TOKEN para todas as requisições Axios automaticamente
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    // É crucial registrar um erro no console se a meta tag do token não for encontrada
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
// --- FIM DA CORREÇÃO ESSENCIAL ---
