import { reactive } from 'vue';

const messages = reactive([]);
let nextId = 0;

export function showToast(message, type = 'success') {
    const id = ++nextId;
    messages.push({ id, message, type });
    window.setTimeout(() => {
        const index = messages.findIndex((item) => item.id === id);
        if (index !== -1) messages.splice(index, 1);
    }, 4000);
}

export function dismissToast(id) {
    const index = messages.findIndex((item) => item.id === id);
    if (index !== -1) messages.splice(index, 1);
}

export function useToast() {
    return { messages, showToast, dismissToast };
}
