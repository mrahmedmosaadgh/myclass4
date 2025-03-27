<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false
    },
    maxWidth: {
        type: String,
        default: '2xl'
    },
    closeable: {
        type: Boolean,
        default: true
    },
    persistent: {
        type: Boolean,
        default: false
    }
});

defineEmits(['close']);
</script>

<template>
    <Teleport to="body">
        <div v-if="showSlot" @click="handleClickOutside">
            <dialog
                ref="dialog"
                class="fixed inset-0 overflow-y-auto px-6 py-8 sm:px-0 z-50"
                :class="[maxWidthClass, { 'pointer-events-none': loading }]"
                @close="close"
            >
                <div
                    ref="modalContent"
                    :class="[
                        'modal-content bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto',
                        { 'modal-enter': isVisible }
                    ]"
                >
                    <!-- Loading overlay -->
                    <div v-if="loading" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center z-50">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                    </div>

                    <!-- Close button -->
                    <button
                        v-if="closeable && !persistent && !loading"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
                        @click="close"
                    >
                        <!-- <span class="sr-only">Close</span> -->
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="modal-body">
                        <slot />
                    </div>
                </div>
            </dialog>
        </div>
    </Teleport>
</template>

<style scoped>
.modal-content {
    max-height: 95vh;
    margin: 1rem auto;
    position: relative;
    opacity: 0;
    transform: scale(0.95);
    transition: all 0.2s ease-out;
}

.modal-content.modal-enter {
    opacity: 1;
    transform: scale(1);
}

.modal-body {
    overflow-y: auto;
    max-height: calc(95vh - 2rem);
    padding: 1.5rem;
}

dialog {
    background: transparent;
    border: none;
    padding: 0;
}

dialog::backdrop {
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    transition: opacity 0.2s ease-out;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    dialog::backdrop {
        background-color: rgba(0, 0, 0, 0.7);
    }
}
</style>






