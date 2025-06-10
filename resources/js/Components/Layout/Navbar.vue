<template>
    <nav class="bg-laranja2 dark:bg-gray-900 dark:border-gray-700">
        <div class="max-w-screen-xl mx-auto px-4 py-3 flex justify-between items-center">

            <div class="block md:hidden w-1/3">
                <img src="/images/logo/prof_ranieri - fundo branco.png" alt="Logo" class="h-12" />
            </div>

            <div class="hidden md:block w-1/3"></div>

            <div class="flex items-center space-x-3 rtl:space-x-reverse w-1/3 justify-end">
                <template v-if="!$page.props.auth.user">
                    <Link href="/login" class="inline-block px-4 py-2 text-lg text-black bg-transparent rounded hover:bg-roxo1 hover:text-prata1 dark:text-blue-500 transition-colors duration-200">
                        Login
                    </Link>
                    <Link href="/register" class="inline-block px-4 py-2 text-lg text-black bg-transparent rounded hover:bg-roxo1 hover:text-prata1 dark:text-blue-500 transition-colors duration-200">
                        Register
                    </Link>
                </template>

                <template v-else>
                    <div ref="dropdownRef" class="relative flex items-center space-x-2 cursor-pointer select-none" @click="toggleDropdown">
                        <!-- Esta é a linha mais importante no frontend, que tenta acessar o avatar -->
                        <img
                            v-if="$page.props.auth.user.profile?.avatar_relation?.url"
                            :src="$page.props.auth.user.profile.avatar_relation.url"
                            alt="Avatar"
                            class="w-8 h-8 rounded-full object-cover"
                        />
                        <img
                            v-else
                            src="https://www.gravatar.com/avatar/?d=mp&f=y"
                            alt="Avatar padrão"
                            class="w-8 h-8 rounded-full object-cover"
                        />
                        <span class="text-gray-900 dark:text-white text-sm font-medium">{{ $page.props.auth.user.name }}</span>

                        <div v-if="dropdownOpen" class="absolute right-0 mt-10 w-44 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-50">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                                <li>
                                    <Link :href="route('profile.show')" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                        Perfil
                                    </Link>
                                </li>
                                <!-- Link para Dashboard no dropdown -->
                                <li>
                                    <Link :href="dashboardRoute" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                        Dashboard
                                    </Link>
                                </li>
                                <li>
                                    <button @click="$inertia.post(route('logout'))" class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                        Logout
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </nav>

    <nav class="bg-roxo1 dark:bg-gray-700 dark:border-gray-600">
        <div class="max-w-screen-xl mx-auto px-4 py-3 flex items-center justify-between">
            <ul :class="menuOpen ? 'block w-full md:flex md:space-x-8 rtl:space-x-reverse' : 'hidden w-full md:flex md:space-x-8 rtl:space-x-reverse'" class="font-medium text-sm text-gray-900 dark:text-white" id="navbar-default">
                <li>
                    <Link href="/" class="inline-block py-2 px-4 rounded text-prata1 hover:bg-laranja2 md:hover:text-prata1 dark:hover:text-blue-500 transition-colors duration-200">
                        Home
                    </Link>
                </li>
                <!-- Link para Dashboard na barra de navegação principal -->
                <li v-if="$page.props.auth.user">
                    <Link :href="dashboardRoute" class="inline-block py-2 px-4 rounded text-prata1 hover:bg-laranja2 md:hover:text-prata1 dark:hover:text-blue-500 transition-colors duration-200">
                        Dashboard
                    </Link>
                </li>
                <li>
                    <Link :href="route('sobre-a-escola')" class="inline-block py-2 px-4 rounded text-prata1 hover:bg-laranja2 md:hover:text-prata1 dark:hover:text-blue-500 transition-colors duration-200">
                        Sobre a Escola
                    </Link>
                </li>
                <li>
                    <Link :href="route('coral-ranieri')" class="inline-block py-2 px-4 rounded text-prata1 hover:bg-laranja2 md:hover:text-prata1 dark:hover:text-blue-500 transition-colors duration-200">
                        Coral Ranieri
                    </Link>
                </li>
                <li>
                    <Link :href="route('gremio')" class="inline-block py-2 px-4 rounded text-prata1 hover:bg-laranja2 md:hover:text-prata1 dark:hover:text-blue-500 transition-colors duration-200">
                        Grêmio
                    </Link>
                </li>
                <li>
                    <Link :href="route('brincando-dialogando')" class="inline-block py-2 px-4 rounded text-prata1 hover:bg-laranja2 md:hover:text-prata1 dark:hover:text-blue-500 transition-colors duration-200">
                        Brincando Dialogando
                    </Link>
                </li>
                <!-- NOVO LINK: Simoninha na Cozinha, totalmente público -->
                <li>
                    <Link :href="route('simoninhanacozinha')" class="inline-block py-2 px-4 rounded text-prata1 hover:bg-laranja2 md:hover:text-prata1 dark:hover:text-blue-500 transition-colors duration-200">
                        Simoninha na Cozinha
                    </Link>
                </li>
                <!-- Link para Galerias: visível APENAS para usuários logados -->
                <li v-if="$page.props.auth.user">
                    <Link :href="route('public.galleries.index')" class="inline-block py-2 px-4 rounded text-prata1 hover:bg-laranja2 md:hover:text-prata1 dark:hover:text-blue-500 transition-colors duration-200">
                        Galerias
                    </Link>
                </li>
            </ul>

            <button @click="menuOpen = !menuOpen" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="navbar-default" :aria-expanded="menuOpen.toString()">
                <span class="sr-only">Open main menu</span>
                <svg v-if="!menuOpen" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg v-else class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </nav>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const menuOpen = ref(false);
const dropdownOpen = ref(false);
const dropdownRef = ref(null);

const page = usePage();

// Propriedade computada para determinar a rota do dashboard
const dashboardRoute = computed(() => {
    const user = page.props.auth.user;
    if (user) {
        if (user.roles && user.roles.some(role => role.name === 'admin')) {
            return route('admin.dashboard');
        }
        if (user.roles && user.roles.some(role => role.name === 'fotografo')) {
            return route('fotografo.dashboard');
        }
    }
    // Rota padrão para dashboard, caso o usuário não seja admin nem fotógrafo
    return route('dashboard');
});


const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        dropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>
