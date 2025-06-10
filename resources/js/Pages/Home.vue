<template>
    <AppLayout>

        <Head title="Home" />

        <!--<template #title>
            <h1>Login no sistema</h1>
        </template>-->

        <div class="mx-auto w-full max-w-[1280px]">
            <section class="relative w-full min-h-[400px] sm:min-h-[420px] md:min-h-[550px] lg:min-h-[650px] xl:min-h-[750px] flex items-center justify-center bg-cover bg-no-repeat bg-center bg-[url('/images/home_slide/slide1.jpg')]">
                <div class="absolute inset-0 mask-fade"></div>
                <div class="relative z-10 px-4 mx-auto max-w-screen-xl text-center flex flex-col justify-center items-center w-full">
                    <h1 class="mb-2 text-3xl font-extrabold tracking-tight leading-snug text-prata1 md:text-4xl lg:text-5xl">
                        SEJA BEM VINDO(A) AO SITE DA ESCOLA P.E.I. PROFESSOR JOSÉ RANIERI!
                    </h1>
                    <p class="mb-6 text-base font-normal text-prata1 lg:text-lg sm:px-10 lg:px-24">
                        <template v-if="$page.props.auth.user">
                            Acesse seu painel!
                        </template>
                        <template v-else>
                            Já tem cadastro?
                        </template>
                    </p>
                    <div class="flex flex-col space-y-3 sm:flex-row sm:justify-center sm:space-y-0">
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="route('dashboard')"
                                class="inline-flex justify-center items-center py-2 px-4 text-md font-medium text-center rounded-lg bg-laranja2 text-preto1 hover:bg-laranja1-hover focus:ring-4 focus:ring-laranja1-focus"
                            >
                                Meu Painel
                                <svg class="w-3 h-3 ms-1 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                </svg>
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                href="/login"
                                class="inline-flex justify-center items-center py-2 px-4 text-md font-medium text-center rounded-lg bg-laranja2 text-preto1 hover:bg-laranja1-hover focus:ring-4 focus:ring-laranja1-focus"
                            >
                                Entre!
                                <svg class="w-3 h-3 ms-1 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                </svg>
                            </Link>
                            <Link
                                href="/register"
                                class="inline-flex justify-center items-center py-2 px-4 sm:ms-2 text-md font-medium text-center rounded-lg bg-roxo2 text-prata1 border border-roxo2 hover:bg-roxo2-hover focus:ring-4 focus:ring-roxo2-focus"
                            >
                                Não? Faça aqui!
                            </Link>
                        </template>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import {Head, Link} from '@inertiajs/vue3'
</script>

<style>
.mask-fade {
    background: linear-gradient(to top, rgba(17, 24, 39, 0.9), rgba(55, 65, 81, 0.7));
    animation: fadeMask 8s ease forwards;
}

@keyframes fadeMask {
    from {
        background: linear-gradient(to top, rgba(17, 24, 39, 0.9), rgba(55, 65, 81, 0.7));
    }
    to {
        background: linear-gradient(to top, rgba(17, 24, 39, 0.6), rgba(55, 65, 81, 0.3));
    }
}
</style>
