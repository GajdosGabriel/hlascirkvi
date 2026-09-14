const listeners = new Map();

export const bus = {
    $on(event, callback) {
        const callbacks = listeners.get(event) ?? new Set();
        callbacks.add(callback);
        listeners.set(event, callbacks);
        return () => this.$off(event, callback);
    },

    $off(event, callback) {
        const callbacks = listeners.get(event);
        callbacks?.delete(callback);
        if (callbacks?.size === 0) listeners.delete(event);
    },

    $emit(event, payload) {
        listeners.get(event)?.forEach((callback) => callback(payload));
    },
};
