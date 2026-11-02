<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Layout/Sidebar.vue';
import Topbar from '@/Components/Layout/Topbar.vue';
import Toast from '@/Components/Toast.vue';
import { showToast } from '@/Composables/useToast';

const page = usePage();
const STORAGE_KEY = 'sundigital_sidebar_collapsed';

const isSidebarCollapsed = ref(false);
const isMobileSidebarOpen = ref(false);

onMounted(() => {
    try {
        isSidebarCollapsed.value = localStorage.getItem(STORAGE_KEY) === '1';
    } catch {
        isSidebarCollapsed.value = false;
    }
});

const toggleSidebar = () => {
    if (typeof window !== 'undefined' && window.innerWidth < 1024) {
        isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
    } else {
        isSidebarCollapsed.value = !isSidebarCollapsed.value;
        try {
            localStorage.setItem(STORAGE_KEY, isSidebarCollapsed.value ? '1' : '0');
        } catch {
            // ignore storage errors
        }
    }
};

const closeMobileSidebar = () => {
    isMobileSidebarOpen.value = false;
};

watch(() => page.url, () => {
    isMobileSidebarOpen.value = false;
});

watch(() => page.props.flash?.success, (message) => {
    if (message) showToast(message, 'success');
}, { immediate: true });

watch(() => page.props.flash?.error, (message) => {
    if (message) showToast(message, 'error');
}, { immediate: true });
</script>

<template>
    <div
        class="app"
        :class="{
            'sidebar-collapsed': isSidebarCollapsed,
            'sidebar-mobile-open': isMobileSidebarOpen,
        }"
    >
        <div
            class="sd-sidebar-backdrop"
            aria-hidden="true"
            @click="closeMobileSidebar"
        ></div>
        <Sidebar @toggle-sidebar="toggleSidebar" />
        <div class="workspace">
            <Topbar @toggle-sidebar="toggleSidebar" />
            <main class="main">
                <Transition name="page" mode="out-in">
                    <div :key="$page.url">
                        <slot />
                    </div>
                </Transition>
            </main>
        </div>
        <Toast />
    </div>
</template>

