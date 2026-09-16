<template>
    <div class="relative">
        <button
            type="button"
            class="relative flex h-9 w-9 items-center justify-center rounded-md text-blue-100 transition-colors hover:bg-blue-800 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-300"
            :aria-expanded="open ? 'true' : 'false'"
            :aria-label="ariaLabel"
            @click="toggle"
        >
            <svg
                class="h-5 w-5"
                :class="bellClass"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>

            <span
                v-if="unreadCount > 0"
                class="absolute right-0.5 top-0.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold leading-none text-white ring-2 ring-blue-900"
            >
                {{ badgeLabel }}
            </span>
        </button>

        <div v-if="open" class="fixed inset-0 z-10" @click="open = false"></div>

        <div
            v-if="open"
            class="absolute right-0 top-full z-20 mt-2 w-96 max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-md border border-gray-200 bg-white shadow-xl"
        >
            <div class="flex items-center justify-between gap-2 border-b border-gray-200 px-4 py-2">
                <p class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                    Notifikácie
                    <span
                        v-if="unreadCount > 0"
                        class="rounded-full bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-800"
                        :title="unreadLabel"
                    >
                        {{ badgeLabel }}
                    </span>
                </p>

                <button
                    v-if="unreadCount > 0"
                    type="button"
                    class="shrink-0 whitespace-nowrap rounded px-1.5 py-1 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-50 hover:text-blue-900 disabled:opacity-50"
                    aria-label="Označiť všetky notifikácie ako prečítané"
                    :disabled="busy"
                    @click="markAllAsRead"
                >
                    Označiť prečítané
                </button>
            </div>

            <p v-if="error" class="px-4 py-3 text-sm text-red-600">
                {{ error }}
                <button type="button" class="underline" @click="load()">Skúsiť znova</button>
            </p>

            <p v-else-if="loading && !groups.length" class="px-4 py-6 text-center text-sm text-gray-500">
                Načítavam…
            </p>

            <p v-else-if="!groups.length" class="px-4 py-6 text-center text-sm text-gray-500">
                Žiadne notifikácie
            </p>

            <ul v-else class="max-h-96 divide-y divide-gray-100 overflow-y-auto">
                <li
                    v-for="group in groups"
                    :key="group.key"
                    class="group relative"
                    :class="{ 'bg-blue-50/60': group.unread }"
                >
                    <component
                        :is="group.link ? 'a' : 'div'"
                        :href="group.link || null"
                        class="flex items-start gap-3 px-4 py-3 pr-16 transition-colors"
                        :class="group.link ? 'hover:bg-gray-50' : ''"
                        @click="openGroup($event, group)"
                    >
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold text-gray-700"
                        >
                            {{ group.logo }}
                        </span>

                        <span class="min-w-0 flex-1">
                            <span
                                class="block text-sm leading-snug text-gray-600"
                                :class="{ 'font-semibold text-gray-900': group.unread }"
                                v-text="group.message"
                            ></span>

                            <span class="mt-0.5 flex items-center gap-2 text-xs text-gray-400">
                                <span>{{ group.time }}</span>
                                <span
                                    v-if="group.count > 1"
                                    class="rounded-full bg-gray-100 px-1.5 py-0.5 font-medium text-gray-600"
                                    :title="group.count + 'x rovnaké hlásenie'"
                                >
                                    {{ group.count }}×
                                </span>
                            </span>
                        </span>
                    </component>

                    <!-- Akcie sa ukazujú pri prejdení myšou, pri dotyku a klávesnici sú vždy dostupné. -->
                    <div
                        class="absolute right-2 top-2 flex items-center gap-0.5 opacity-0 transition-opacity focus-within:opacity-100 group-hover:opacity-100 max-md:opacity-100"
                    >
                        <button
                            type="button"
                            class="flex h-7 w-7 items-center justify-center rounded text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50"
                            :title="group.unread ? 'Označiť ako prečítané' : 'Označiť ako neprečítané'"
                            :aria-label="group.unread ? 'Označiť ako prečítané' : 'Označiť ako neprečítané'"
                            :disabled="busy"
                            @click.stop.prevent="toggleRead(group)"
                        >
                            <span
                                class="h-2.5 w-2.5 rounded-full"
                                :class="group.unread ? 'bg-blue-600' : 'border border-gray-400'"
                            ></span>
                        </button>

                        <button
                            type="button"
                            class="flex h-7 w-7 items-center justify-center rounded text-gray-400 transition-colors hover:bg-gray-100 hover:text-red-600 disabled:opacity-50"
                            title="Zmazať"
                            aria-label="Zmazať notifikáciu"
                            :disabled="busy"
                            @click.stop.prevent="remove(group)"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </div>
                </li>

                <li v-if="hasMore" class="px-4 py-2 text-center">
                    <button
                        type="button"
                        class="text-xs font-medium text-blue-700 transition-colors hover:text-blue-900 disabled:opacity-50"
                        :disabled="loading"
                        @click="load(page + 1)"
                    >
                        {{ loading ? 'Načítavam…' : 'Zobraziť staršie' }}
                    </button>
                </li>
            </ul>

            <div
                v-if="groups.length"
                class="flex items-center justify-end gap-3 border-t border-gray-200 px-4 py-2"
            >
                <button
                    v-if="hasRead"
                    type="button"
                    class="text-xs text-gray-500 transition-colors hover:text-gray-800 disabled:opacity-50"
                    :disabled="busy"
                    @click="removeAll('read')"
                >
                    Zmazať prečítané
                </button>

                <button
                    type="button"
                    class="text-xs text-gray-500 transition-colors hover:text-red-600 disabled:opacity-50"
                    :disabled="busy"
                    @click="removeAll()"
                >
                    Zmazať všetky
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { createdMixin } from "../mixins/createdMixin";

export default {
    props: ["user"],
    mixins: [createdMixin],

    data() {
        return {
            open: false,
            loading: false,
            busy: false,
            error: "",
            items: [],
            page: 1,
            lastPage: 1,
            // Kým sa zoznam nedotiahne, drží odznak počet z /api/user.
            unread: null
        };
    },

    computed: {
        unreadCount() {
            if (this.unread !== null) {
                return this.unread;
            }

            return this.user && this.user.countNotifycation
                ? this.user.countNotifycation
                : 0;
        },

        /**
         * Rovnaké hlásenia sa spájajú do jedného riadka s počtom. Jeden
         * spamový úmysel vedel do zvončeka poslať desať totožných položiek.
         */
        groups() {
            const groups = [];
            const byKey = {};

            this.items.forEach(item => {
                const data = item.data || {};
                const key = (data.link || "") + "|" + (data.message || "");

                if (!byKey[key]) {
                    byKey[key] = {
                        key: key,
                        ids: [],
                        unreadIds: [],
                        count: 0,
                        message: data.message,
                        link: data.link,
                        logo: data.logo || this.fallbackLogo(item),
                        time: item.created_for_humans,
                        unread: false
                    };
                    groups.push(byKey[key]);
                }

                const group = byKey[key];
                group.ids.push(item.id);
                group.count += 1;

                if (!item.read_at) {
                    group.unreadIds.push(item.id);
                    group.unread = true;
                }
            });

            return groups;
        },

        hasRead() {
            return this.items.some(item => item.read_at);
        },

        hasMore() {
            return this.page < this.lastPage;
        },

        bellClass() {
            return this.unreadCount > 0 ? "text-red-400" : "";
        },

        badgeLabel() {
            return this.unreadCount > 99 ? "99+" : this.unreadCount;
        },

        /** Skloňovanie: 1 neprečítaná, 2-4 neprečítané, 5+ neprečítaných. */
        unreadLabel() {
            const count = this.unreadCount;
            let word = "neprečítaných";

            if (count === 1) {
                word = "neprečítaná";
            } else if (count < 5) {
                word = "neprečítané";
            }

            return count + " " + word;
        },

        ariaLabel() {
            return this.unreadCount > 0
                ? "Notifikácie (" + this.unreadLabel + ")"
                : "Notifikácie";
        }
    },

    methods: {
        toggle() {
            this.open = !this.open;

            // Zoznam sa ťahá až pri otvorení a pri ďalšom otvorení sa obnoví.
            if (this.open) {
                this.load();
            }
        },

        load(page) {
            this.loading = true;
            this.error = "";

            return axios
                .get("/api/notifications", { params: { page: page || 1 } })
                .then(response => {
                    const body = response.data;
                    const meta = body.meta || {};

                    this.items = page > 1 ? this.items.concat(body.data) : body.data;
                    this.page = meta.current_page || 1;
                    this.lastPage = meta.last_page || 1;
                    this.unread = meta.unread || 0;
                })
                .catch(() => {
                    this.error = "Notifikácie sa nepodarilo načítať.";
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        /**
         * Kliknutie na položku ju označí za prečítanú. Na odkaz sa čaká, kým
         * zápis dobehne — inak by prehliadač odchodom na cieľovú stránku
         * požiadavku zrušil a notifikácia by zostala neprečítaná.
         */
        openGroup(event, group) {
            if (!group.unread) {
                return;
            }

            const ids = group.unreadIds;

            this.setRead(ids, true);

            const saved = axios.post("/api/notifications/read", { ids: ids });

            // Otvorenie na novej karte (Ctrl/Cmd, koliesko) nechávame tak.
            if (!group.link || event.ctrlKey || event.metaKey || event.shiftKey) {
                return;
            }

            event.preventDefault();
            saved.catch(() => {}).then(() => {
                window.location.href = group.link;
            });
        },

        toggleRead(group) {
            const read = group.unread;
            const ids = read ? group.unreadIds : group.ids;

            this.setRead(ids, read);
            this.request(
                axios.post("/api/notifications/" + (read ? "read" : "unread"), {
                    ids: ids
                })
            );
        },

        markAllAsRead() {
            this.setRead(this.items.map(item => item.id), true);
            // Server označí aj to, čo v zvončeku ešte nie je načítané.
            this.unread = 0;
            this.request(axios.post("/api/notifications/read"));
        },

        remove(group) {
            const removed = this.items.filter(item => group.ids.indexOf(item.id) !== -1);

            this.items = this.items.filter(item => group.ids.indexOf(item.id) === -1);
            this.unread = Math.max(
                0,
                this.unreadCount - removed.filter(item => !item.read_at).length
            );

            this.request(
                axios.delete("/api/notifications", { data: { ids: group.ids } })
            );
        },

        removeAll(only) {
            const question = only
                ? "Zmazať všetky prečítané notifikácie?"
                : "Zmazať všetky notifikácie?";

            if (!window.confirm(question)) {
                return;
            }

            if (only === "read") {
                this.items = this.items.filter(item => !item.read_at);
            } else {
                this.items = [];
                this.unread = 0;
            }

            this.request(
                axios.delete("/api/notifications", { data: { only: only } })
            );
        },

        /** Optimistická zmena stavu v zozname, server ju len potvrdzuje. */
        setRead(ids, read) {
            const stamp = new Date().toISOString();
            let delta = 0;

            this.items = this.items.map(item => {
                if (ids.indexOf(item.id) === -1) {
                    return item;
                }

                // Odznak sa posúva o skutočnú zmenu — neprečítané môžu byť
                // aj na ďalších stranách, ktoré zvonček nemá načítané.
                if (read && !item.read_at) {
                    delta -= 1;
                }

                if (!read && item.read_at) {
                    delta += 1;
                }

                return Object.assign({}, item, { read_at: read ? stamp : null });
            });

            this.unread = Math.max(0, this.unreadCount + delta);
        },

        /**
         * Keď zápis zlyhá, zoznam sa natiahne odznova — inak by v ňom zostal
         * stav, ktorý na serveri nie je.
         */
        request(promise) {
            this.busy = true;

            return promise
                .catch(() => {
                    this.error = "Akciu sa nepodarilo uložiť.";
                    return this.load();
                })
                .finally(() => {
                    this.busy = false;
                });
        },

        fallbackLogo(item) {
            const message = (item.data && item.data.message) || "";
            const first = message.trim().charAt(0);

            return first ? first.toUpperCase() : "•";
        }
    }
};
</script>
