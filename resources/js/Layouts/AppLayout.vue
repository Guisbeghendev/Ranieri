<template>
    <div class="flex flex-col min-h-screen animated-bg">
        <div class="hidden md:block">
            <Header />
        </div>

        <div>
            <Navbar />
        </div>

        <main class="flex-grow flex justify-center px-4">
            <div class="w-full bg-white rounded-lg shadow-7x1 m-5 p-2 max-w-6xl dark:bg-gray-800">
                <!-- Slot nomeado para título (opcional) -->
                <div v-if="$slots.title" class="mb-4 border-b pb-2 text-2xl font-bold text-center text-laranja2 dark:text-gray-200">
                    <slot name="title" />
                </div>

                <!-- Conteúdo principal -->
                <slot />
            </div>
        </main>

        <div>
            <Footer />
        </div>
    </div>
</template>

<script setup>
import Header from '@/Components/Layout/Header.vue';
import Navbar from '@/Components/Layout/Navbar.vue';
import Footer from '@/Components/Layout/Footer.vue';
</script>

<style scoped>
.animated-bg {
    background: linear-gradient(
        270deg,
        #c89c20, /* laranja 1 */
        #cd862a, /* laranja 2 */
        #c9583e, /* laranja 3 */
        #61152d, /* roxo 1 */
        #681630  /* roxo 2 */
    );
    background-size: 1000% 1000%;
    animation: bgGradientMove 30s ease infinite;
}

@keyframes bgGradientMove {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}
</style>
