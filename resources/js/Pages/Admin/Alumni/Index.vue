<template>
    <AppLayout :title="__('Alumni List')">
        <template #header>
            {{ __('Alumni List') }}
        </template>
        <Breadcrumb>
            <BreadcrumbLink :title="__('Alumni List')" />
        </Breadcrumb>

        <div>
            <page-header class="flex-col sm:flex-row">
                {{ __('Alumni') }}
                <template #content>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <global-button preserve-scroll :loading="false" :url="route('alumni.create')" type="link" theme="primary">
                            {{ __('Batch Promote') }}
                        </global-button>
                    </div>
                </template>
            </page-header>

            <div class="mb-4">
                <form class="items-center grid grid-cols-1 md:grid-cols-7 gap-6" @submit.prevent="filterData()">
                    <div class="flex col-span-2">
                        <div class="relative w-full">
                            <global-input type="search" v-model="filter.keyword"
                                class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400"
                                :placeholder="__('Keyword')" />
                            <button type="submit"
                                class="absolute top-1 right-0 p-2.5 text-sm font-medium text-white bg-blue-700 rounded-r-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <MagnifyingGlassIcon class="w-5 h-5" />
                                <span class="sr-only">
                                    {{ __('Search') }}
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <a-select class="width-100" size="large" v-model:value="filter.status"
                            :placeholder="__('Filter Status')" :options="statusOptions" allow-clear
                            @change="filterData">
                        </a-select>
                    </div>
                    <div class="col-span-1">
                        <global-button :loading="loading" type="submit" theme="primary">
                            {{ __('Search') }}
                        </global-button>
                    </div>
                </form>
            </div>

            <global-table>
                <template #head>
                    <th class="py-4 px-5">{{ __('Name') }}</th>
                    <th class="py-4 px-5">{{ __('Jurusan') }}</th>
                    <th class="py-4 px-5">{{ __('Tahun Lulus') }}</th>
                    <th class="py-4 px-5">{{ __('Email') }}</th>
                    <th class="py-4 px-5">{{ __('Phone') }}</th>
                    <th class="py-4 px-5">{{ __('Status') }}</th>
                    <th width="10%" class="py-4 px-5">{{ __('Action') }}</th>
                </template>
                <template #body>
                    <template v-if="alumnis.data.length > 0">
                        <template v-for="alumni in alumnis.data" :key="alumni.id">
                            <tr class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td scope="row"
                                    class="flex items-center py-4 px-5 text-gray-900 whitespace-nowrap dark:text-white">
                                    <td-user-show :image="alumni.profile_photo_url" :name="alumni.name"
                                        :email="alumni.email" />
                                </td>
                                <td class="py-4 px-5">
                                    {{ alumni.jurusan ?? '-' }}
                                </td>
                                <td class="py-4 px-5">
                                    {{ alumni.tahun_lulus }}
                                </td>
                                <td class="py-4 px-5">
                                    {{ alumni.email ?? '-' }}
                                </td>
                                <td class="py-4 px-5">
                                    {{ alumni.phone ?? '-' }}
                                </td>
                                <td class="py-4 px-5">
                                    <span :class="alumni.status === 'active'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                                        class="text-xs font-bold mr-2 px-2.5 py-0.5 rounded">
                                        {{ alumni.status === 'active' ? __('Active') : __('Inactive') }}
                                    </span>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="flex gap-2">
                                        <a :href="alumni.email ? `mailto:${alumni.email}` : '#'"
                                            class="group relative" :id="'email' + alumni.id">
                                            <EnvelopeIcon class="w-6 h-6 text-purple-400 hover:text-purple-300" />
                                            <tool-tip :text="__('Send Email')" />
                                        </a>
                                        <button type="button" @click="destroy(alumni.id)" class="group relative"
                                            :id="'delete' + alumni.id">
                                            <trash-icon class="w-6 h-6 text-red-400 hover:text-red-300" />
                                            <tool-tip :text="__('Delete')" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>
                    <template v-else>
                        <tr>
                            <td colspan="7" class="text-center p-4">
                                <NothingFound asShow="div" />
                            </td>
                        </tr>
                    </template>
                </template>
            </global-table>
            <div class="flex justify-center">
                <pagination class="mt-6 mb-6" :links="alumnis.links" />
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import ToolTip from "@/Shared/ToolTip.vue";
import Pagination from "@/Shared/Admin/Pagination.vue";
import TdUserShow from "@/Shared/TdUserShow.vue";
import NothingFound from "@/Shared/NothingFound.vue";
import { MagnifyingGlassIcon, TrashIcon, EnvelopeIcon } from '@heroicons/vue/24/outline'

export default {
    components: {
        AppLayout,
        ToolTip,
        TdUserShow,
        TrashIcon,
        MagnifyingGlassIcon,
        EnvelopeIcon,
        Pagination,
        NothingFound,
    },
    props: {
        alumnis: Object,
        filter_data: Object,
    },
    data() {
        return {
            loading: false,
            statusOptions: [
                { value: '', label: this.__('All Status') },
                { value: 'active', label: this.__('Active') },
                { value: 'inactive', label: this.__('Inactive') },
            ],
            filter: {
                keyword: this.filter_data.keyword ?? null,
                status: this.filter_data.status ?? null,
            },
        };
    },
    methods: {
        filterData() {
            this.loading = true;
            this.$inertia.get(
                this.route('alumni.index'),
                this.filter,
                {
                    preserveScroll: true,
                    onFinish: (visit) => {
                        this.loading = false;
                    },
                }
            );
        },
        destroy(id) {
            if (confirm(this.__('Are you sure ?'))) {
                this.$inertia.delete(this.route('alumni.destroy', id), {
                    preserveScroll: true,
                });
            }
        },
    },
};
</script>

<style lang="scss" scoped></style>