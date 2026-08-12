<template>
    <AppLayout :title="__('Administration Report')">
        <Breadcrumb>
            <BreadcrumbLink :href="route('reports.index')" title="Report" />
            <BreadcrumbLink :title="__('Administration')" />
        </Breadcrumb>

        <div class="py-12">
            <page-header class="flex justify-between items-center">
                <div>
                    <h2 class="dark:text-gray-400 mb-0">
                        {{ __('Administration Report') }}
                    </h2>
                </div>
            </page-header>
            <div class="mb-3 ml-0.5">
                <!-- <form class="grid grid-cols-1 md:grid-cols-8 gap-6 items-center" @submit.prevent="filterData()">
                    <div class="col-span-2">
                        <global-input id="search" v-model="filter.keyword" type="search"
                            class="block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400"
                            :placeholder="__('Search: name/email')" :error="$page.props.errors.keyword" />
                    </div>
                    <div class="col-span-2">
                        <a-select size="large" class="w-full" v-model:value="filter.department" show-search placeholder="{{ __('Select a department') }}" :options="options2" :filter-option="filterOption">
                        </a-select>
                    </div>
                    <div class="col-span-2">
                        <div class="flex items-center">
                            <label class="mr-2">{{ __('From') }}:</label>
                            <input type="date" v-model="filter.start_date" class="p-2 border rounded" />
                        </div>
                    </div>
                    <div class="col-span-2">
                        <div class="flex items-center">
                            <label class="mr-2">{{ __('To') }}:</label>
                            <input type="date" v-model="filter.end_date" class="p-2 border rounded" />
                        </div>
                    </div>
                    <div class="col-span-2">
                        <global-button :loading="filter.processing" type="submit" class="text-white bg-blue-500 dark:bg-blue-500 hover:bg-blue-500 active:bg-blue-600 dark:active:bg-blue-600 focus:border-blue-600 dark:focus:border-blue-600 focus:ring focus:ring-blue-300 dark:focus:ring-blue-300">
                            {{ __('Search') }}
                        </global-button>
                    </div>
                </form> -->

                <form @submit.prevent="filterData">
                    <div class="flex items-center">
                        <input
                            type="text"
                            v-model="filter.name"
                            id="name"
                            placeholder="Name"
                            class="p-2 border rounded"
                        />

                        <div class="ml-4 flex items-center">
                            <label for="start_date" class="mr-2"
                                >{{ __("From") }}:</label
                            >
                            <input
                                type="date"
                                v-model="filter.start_date"
                                id="start_date"
                                class="p-2 border rounded"
                            />
                        </div>

                        <div class="ml-4 flex items-center">
                            <label for="end_date" class="mr-2"
                                >{{ __("To") }}:</label
                            >
                            <input
                                type="date"
                                v-model="filter.end_date"
                                id="end_date"
                                class="p-2 border rounded mr-4"
                            />
                        </div>

                        <button
                            type="submit"
                            class="ml-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                        >
                            {{ __("Filter") }}
                        </button>
                    </div>
                </form>

            </div>
            <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-lg">
                <div class="flex justify-between items-center px-6 py-4">
                    <div class="text-gray-900 dark:text-gray-400 text-lg font-bold">{{ __('Attendance List') }}</div>
                    <global-button :loading="false" @click="exportAttendance" type="button" theme="sky">{{ __('Export') }}</global-button>
                </div>
                <div class="overflow-x-auto shadow-md rounded-lg">
                    <table class="w-full text-sm text-center text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                            <tr>
                                <th rowspan="2" class="py-4 px-6">No</th>
                                <th rowspan="2" class="py-4 px-6">{{ __('Name') }}</th>
                                <th rowspan="2" class="py-4 px-6">{{ __('Department') }}</th>
                                <th :colspan="dates.length" class="py-4 px-6">{{ __('Dates') }}</th>
                                <th rowspan="2" class="py-4 px-6">Hadir</th>
                                <th rowspan="2" class="py-4 px-6">Sakit</th>
                                <th rowspan="2" class="py-4 px-6">Ijin</th>
                                <th rowspan="2" class="py-4 px-6">Alfa</th>
                            </tr>
                            <tr>
                                <th v-for="(date, idx) in dates" :key="idx" class="py-3 px-5">{{ date }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="administrationsAttendance.length > 0">
                                <template v-for="(admin, index) in administrationsAttendance" :key="admin.id">
                                    <tr class="border-b border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="py-4 px-6 font-medium">{{ index + 1 }}</td>
                                        <td class="py-4 px-6 font-medium text-left">{{ admin.user_name }}</td>
                                        <td class="py-4 px-6">{{ admin.department }}</td>
                                        <td v-for="(status, i) in admin.attendance" :key="i" class="py-4 px-6">{{ status || '-' }}</td>
                                        <td class="py-4 px-6 font-semibold">{{ admin.summary.hadir }}</td>
                                        <td class="py-4 px-6 font-semibold">{{ admin.summary.sakit }}</td>
                                        <td class="py-4 px-6 font-semibold">{{ admin.summary.ijin }}</td>
                                        <td class="py-4 px-6 font-semibold">{{ admin.summary.alfa }}</td>
                                    </tr>
                                </template>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="{{ 3 + dates.length + 4 }}" class="text-center p-4"><NothingFound asShow="div" /></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex justify-center">
                <pagination class="mt-6" :links="administrations.links" />
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Shared/Admin/Pagination.vue";
import { useForm } from "@inertiajs/inertia-vue3";
import NothingFound from "@/Shared/NothingFound.vue";
import { ref } from "vue";
import axios from "axios";

export default {
    components: {
        AppLayout,
        Pagination,
        NothingFound,
    },
    props: {
        administrations: Object,
        administrationsAttendance: Array,
        dates: Array,
        departments: Array,
        filter_data: Object,
    },
    data() {
        const today = new Date();
        const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0,10);
        const endOfMonth = new Date(today.getFullYear(), today.getMonth()+1, 0).toISOString().slice(0,10);
        return {
            filter: useForm({
                keyword: this.filter_data.keyword ?? "",
                department: this.filter_data.department ?? "",
                start_date: this.filter_data.start_date ?? startOfMonth,
                end_date: this.filter_data.end_date ?? endOfMonth,
            }),
            loading: false,
            options2: [],
            export_data: useForm({ type: "PDF", department: "all" }),
        };
    },
    created() {
        this.options2.push({ value: "", label: "All" });
        for (const [key, value] of Object.entries(this.departments)) {
            this.options2.push({ value: value.slug, label: value.name });
        }
    },
    setup(props) {
        const administrationsAttendance = ref(props.administrationsAttendance || []);
        const dates = ref(props.dates || []);
        return { administrationsAttendance, dates };
    },
    methods: {
        filterOption(input, option) {
            return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
        },
        exportSubmit() {
            let exportData = {};
            if (this.filter.department !== "") {
                exportData.departement = this.filter.department;
            }

            axios({
                url: this.route("all.administration.export"),
                method: "POST",
                data: { ...this.export_data, ...exportData },
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector("meta[name=\"csrf-token\"]")
                        .getAttribute("content"),
                },
                responseType: "blob",
            }).then((response) => {
                var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                var fileLink = document.createElement("a");

                fileLink.href = fileURL;
                fileLink.setAttribute("download", "administration-report.xlsx");
                document.body.appendChild(fileLink);

                fileLink.click();
            });
        },
        filterData() {
            this.loading = true;
            const params = {
                keyword: this.filter.keyword || null,
                department: this.filter.department || null,
                start_date: this.filter.start_date,
                end_date: this.filter.end_date,
            };
            this.$inertia.get(this.route("report.administration"), params, {
                preserveScroll: true,
                onFinish: (visit) => {
                    this.loading = false;
                    this.administrationsAttendance = visit.props.administrationsAttendance || this.administrationsAttendance;
                    this.dates = visit.props.dates || this.dates;
                }
            });
        },
        exportAttendance() {
            this.loading = true;
            let exportData = {
                start_date: this.filter.start_date,
                end_date: this.filter.end_date,
                type: 'xlsx'
            };
            if (this.filter.keyword?.trim()) {
                exportData.keyword = this.filter.keyword.trim();
            }
            if (this.filter.department) {
                exportData.department = this.filter.department;
            }

            axios({
                url: this.route('report.administration.export'),
                method: 'POST',
                data: exportData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                responseType: 'blob'
            }).then((response) => {
                var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                var fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', 'administration_attendance_' + this.filter.start_date + '_' + this.filter.end_date + '.xlsx');
                document.body.appendChild(fileLink);
                fileLink.click();
            }).catch((e) => {
                console.error(e);
            }).finally(() => { this.loading = false; });
        },
    },
};
</script>

<style scoped>
.course {
    margin-bottom: 20px;
}
</style>
