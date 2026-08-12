<template>
    <AppLayout :title="__('Alumni List')">
        <Breadcrumb>
            <BreadcrumbLink :title=" __('Alumni List')" />
        </Breadcrumb>

        <!-- removed undefined preview component -->
        <div >
            <page-header >
                <h2 class="dark:text-gray-400 mb-0">
                    {{ __('Alumnies') }}
                    <span class="bg-blue-500 text-white rounded px-1 text-xs py-0.5">
                        {{ alumnies.total }}
                    </span>
                </h2>
            </page-header>
            <div class="mb-3 ml-0.5">
                <form class="items-center grid grid-cols-1 md:grid-cols-7 gap-6" @submit.prevent="filterData()">
                    <div class="col-span-2">
                        <div class="relative w-full">
                            <global-input type="search" v-model="filter.keyword"
                                class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400"
                                :placeholder="__('Keyword')" />
                            <button type="submit"
                                class="absolute top-1 right-0 p-2.5 text-sm font-medium text-white bg-blue-700 rounded-r-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-300">
                                <span class="sr-only">
                                    {{ __('Search') }}
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <a-select class="width-100" size="large" v-model:value="filter.course" show-search
                            :placeholder="__('Select a course')" :options="options" :filter-option="filterOption"
                            @focus="handleFocus" @blur="handleBlur" @change="handleChange">
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
                    <th class="py-5 px-5">{{ __('Name') }}</th>
                    <th class="py-5">{{ __('Guardian Name') }}</th>
                    <th class="py-5">{{ __('Contact Number') }}</th>
                    <th width="10%" class="py-5">{{ __('Action') }}</th>
                </template>
                <template #body>
                    <template v-if="alumnies.data.length > 0">
                        <template v-for="alumni in alumnies.data" :key="alumni.id">
                            <tr class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <th scope="row"
                                    class="flex items-center py-5 px-5 text-gray-900 whitespace-nowrap dark:text-white">
                                    <img class="w-10 h-10 rounded-full" :src="alumni.user && alumni.user.profile_photo_url ? alumni.user.profile_photo_url : '/assets/backend/images/default-avatar.png'" alt="" />
                                    <div class="pl-3">
                                        <div class="text-base font-semibold dark:text-gray-400">
                                            {{ alumni.user ? alumni.user.name : '-' }}
                                        </div>
                                        <div class="font-normal text-gray-500 dark:text-gray-400">
                                            {{ alumni.user ? alumni.user.email : '-' }}
                                        </div>
                                    </div>
                                </th>
                                <td class="py-5">
                                    {{ alumni.parent ? alumni.parent.name : '-' }}
                                </td>
                                <td class="py-5">
                                    {{ alumni.user ? alumni.user.phone : '-' }}
                                </td>
                                <td class="py-5">
                                    <a :href="alumni.user ? `mailto:${alumni.user.email}` : '#'
                                        "
                                        class="py-2 px-3 text-xs font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                        {{ __('Send Email') }}
                                    </a>
                                </td>
                            </tr>
                        </template>
                    </template>
                    <template v-else>
                        <NothingFound asShow="tr" />
                    </template>
                </template>
            </global-table>
            <div class="flex justify-center">
                <pagination class="mt-6" :links="alumnies.links" />
            </div>
        </div>
    </AppLayout>
</template>

<script>
    import AppLayout from "@/Layouts/AppLayout.vue";
    import Pagination from "@/Shared/Admin/Pagination.vue";
        import NothingFound from "@/Shared/NothingFound.vue";
    import Multiselect from '@vueform/multiselect';
    import '@vueform/multiselect/themes/default.css';

    export default {
        components: {
            AppLayout,
            Pagination,
            NothingFound,
            Multiselect
        },
        props: {
            alumnies: Object,
            filter_data: Object,
            classes: Object
        },
        data() {
            return {
                loading: false,
                options: [],
                filter: {
                    keyword: this.filter_data.keyword ?? null,
                    course: this.filter_data.course ?? null,
                },
            };
        },
        created() {
            // prepare options for course filter
            this.options.push({ value: "", label: "All" });
            for (const [key, value] of Object.entries(this.classes)) {
                this.options.push({ value: value.slug, label: value.name });
            }
        },
        methods: {
            filterData() {
                this.loading = true;
                this.$inertia.get(
                    this.route('alumni.admission.all'),
                    this.filter,
                    {
                        preserveScroll: true,
                        onFinish: () => {
                            this.loading = false;
                        }
                    }
                );
            },
            filterOption(input, option) {
                return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
            },
            handleFocus() {},
            handleBlur() {},
            handleChange() {}
        },
    };
</script>
