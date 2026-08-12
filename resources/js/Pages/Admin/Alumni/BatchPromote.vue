<template>
    <AppLayout :title="__('Batch Promote Alumni')">
        <template #header>
            {{ __('Batch Promote Alumni') }}
        </template>
        <Breadcrumb>
            <BreadcrumbLink :title="__('Alumni')" :href="route('alumni.index')" />
            <BreadcrumbLink :title="__('Batch Promote')" />
        </Breadcrumb>

        <div>
            <page-header class="flex-col sm:flex-row">
                {{ __('Batch Promote Siswa Kelas 12') }}
                <template #content>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <global-button :loading="false" @click="selectAll()" type="button" theme="sky">
                            {{ allSelected ? __('Unselect All') : __('Select All') }}
                        </global-button>
                        <global-button preserve-scroll :loading="false" :url="route('alumni.index')" type="link" theme="secondary">
                            {{ __('Back') }}
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
                        <a-select class="width-100" size="large" v-model:value="filter.course_id"
                            :placeholder="__('Filter Kelas')" :options="courseOptions" allow-clear
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

            <!-- Batch Promote Form -->
            <div class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                            {{ __('Tahun Lulus') }} <span class="text-red-500">*</span>
                        </label>
                        <global-input type="number" v-model="form.tahun_lulus" min="2000" max="2100"
                            class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400"
                            :placeholder="__('Contoh: 2026')" />
                        <p v-if="form.errors.tahun_lulus" class="text-red-500 text-xs mt-1">{{ form.errors.tahun_lulus }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                            {{ __('Kelas XII') }}
                        </label>
                        <a-select class="width-100" size="large" v-model:value="form.course_id"
                            :placeholder="__('Pilih Kelas (Opsional)')" :options="courseOptions" allow-clear>
                        </a-select>
                    </div>
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-4">
                            <global-button :loading="submitLoading" type="button" theme="primary" @click="submitPromote()">
                                {{ __('Promote ke Alumni') }} ({{ selectedIds.length }})
                            </global-button>
                            <span v-if="form.errors.error" class="text-red-500 text-sm">{{ form.errors.error }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <global-table>
                <template #head>
                    <th class="py-4 px-5">{{ __('Select') }}</th>
                    <th class="py-4 px-5">{{ __('Name') }}</th>
                    <th class="py-4 px-5">{{ __('No. Ref') }}</th>
                    <th class="py-4 px-5">{{ __('Kelas') }}</th>
                    <th class="py-4 px-5">{{ __('Email') }}</th>
                    <th class="py-4 px-5">{{ __('Status Alumni') }}</th>
                </template>
                <template #body>
                    <template v-if="students.length > 0">
                        <template v-for="student in students" :key="student.id">
                            <tr class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                :class="{ 'bg-blue-50 dark:bg-gray-600': isSelected(student.id) }">
                                <td class="py-4 px-5">
                                    <input type="checkbox" :value="student.id" v-model="selectedIds"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                </td>
                                <td scope="row"
                                    class="flex items-center py-4 px-5 text-gray-900 whitespace-nowrap dark:text-white">
                                    <td-user-show :image="student.profile_photo_url" :name="student.name"
                                        :email="student.email" />
                                </td>
                                <td class="py-4 px-5">
                                    {{ student.id_reference ?? '-' }}
                                </td>
                                <td class="py-4 px-5">
                                    <template v-if="student.courses.length > 0">
                                        <span v-for="(uc, index) in student.courses" :key="uc.id">
                                            {{ uc.course ? uc.course.name : '' }}<template v-if="student.courses.length != index + 1">, </template>
                                        </span>
                                    </template>
                                    <template v-else>-</template>
                                </td>
                                <td class="py-4 px-5">
                                    {{ student.email ?? '-' }}
                                </td>
                                <td class="py-4 px-5">
                                    <span v-if="student.is_alumni"
                                        class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                        {{ __('Sudah Alumni') }}
                                    </span>
                                    <span v-else
                                        class="bg-gray-100 text-gray-800 text-xs font-bold px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">
                                        {{ __('Belum') }}
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </template>
                    <template v-else>
                        <tr>
                            <td colspan="6" class="text-center p-4">
                                <NothingFound asShow="div" />
                            </td>
                        </tr>
                    </template>
                </template>
            </global-table>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import ToolTip from "@/Shared/ToolTip.vue";
import Pagination from "@/Shared/Admin/Pagination.vue";
import TdUserShow from "@/Shared/TdUserShow.vue";
import NothingFound from "@/Shared/NothingFound.vue";
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { useForm } from "@inertiajs/inertia-vue3";

export default {
    components: {
        AppLayout,
        ToolTip,
        TdUserShow,
        MagnifyingGlassIcon,
        Pagination,
        NothingFound,
    },
    props: {
        students: Array,
        xii_courses: Array,
        filter_data: Object,
    },
    data() {
        return {
            loading: false,
            submitLoading: false,
            selectedIds: [],
            filter: {
                keyword: this.filter_data?.keyword ?? null,
                course_id: this.filter_data?.course_id ?? null,
            },
            form: useForm({
                student_ids: [],
                tahun_lulus: new Date().getFullYear(),
                course_id: null,
            }),
        };
    },
    computed: {
        allSelected() {
            return this.students.length > 0 && this.selectedIds.length === this.students.length;
        },
        courseOptions() {
            const options = [{ value: '', label: this.__('Semua Kelas') }];
            for (const course of this.xii_courses) {
                options.push({ value: course.id, label: course.name });
            }
            return options;
        },
    },
    methods: {
        filterData() {
            this.loading = true;
            this.$inertia.get(
                this.route('alumni.create'),
                this.filter,
                {
                    preserveScroll: true,
                    onFinish: (visit) => {
                        this.loading = false;
                    },
                }
            );
        },
        selectAll() {
            if (this.allSelected) {
                this.selectedIds = [];
            } else {
                this.selectedIds = this.students.map((s) => s.id);
            }
        },
        isSelected(id) {
            return this.selectedIds.includes(id);
        },
        submitPromote() {
            if (this.selectedIds.length === 0) {
                alert(this.__('Pilih minimal satu siswa'));
                return;
            }
            if (!this.form.tahun_lulus) {
                alert(this.__('Tahun lulus wajib diisi'));
                return;
            }
            this.form.student_ids = [...this.selectedIds];
            this.form.course_id = this.form.course_id || null;
            this.submitLoading = true;
            this.form.post(this.route('alumni.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.submitLoading = false;
                    this.selectedIds = [];
                },
                onError: (errors) => {
                    this.submitLoading = false;
                },
                onFinish: () => {
                    this.submitLoading = false;
                },
            });
        },
    },
};
</script>

<style lang="scss" scoped></style>