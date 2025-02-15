<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const options = ref({
    chart: {
        id: 'vuechart-example',
    },
    xaxis: {
        type: 'datetime',
    },
});

const data = ref({});
const series = ref([]);

const sku_list = ref([]);

const dates = ref([]);
const glances = ref({});

const initialize = () => {
    axios.get('/api/admin/dashboard/inventory/delta').then((response) => {
        if (response.data.success == true) {
            data.value = response.data.inventory_delta;

            dates.value = response.data.dates;
            glances.value = response.data.glances;
            sku_list.value = response.data.sku_list;
            addChart('bonnie');
            addChart('sexy');
            addChart('blooming');
            addChart('picnic');
            addChart('wood');
            addChart('sum');
        }
    });
};

const addChart = (sku) => {
    let sku_data = {
        name: sku,
        data: data.value[sku],
    };
    series.value.push(sku_data);
};

const number = (number) => {
    return String(number).replace(/^\d+/, (number) =>
        [...number]
            .map(
                (digit, index, digits) =>
                    (!index || (digits.length - index) % 3 ? '' : ',') + digit,
            )
            .join(''),
    );
};

const download = (dates) => {
    return axios
        .get('/api/export/inventory', {
            params: {
                //type: this.type,
                //date_to: this.date_to,
                //date_from: this.date_from
            },
            responseType: 'blob',
        })
        .then((response) => {
            console.log(response.data);
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'delta.xlsx'); //or any other extension
            document.body.appendChild(link);
            link.click();
        })
        .catch((error) => {
            //this.loading = false
            console.log('updating data error', error.response.data.errors);
            let errors = error.response.data.errors;
            let error_message = errors[Object.keys(errors)[0]];
        });
};

initialize();
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard
            </h2>
        </template>
        <div class="mx-auto flex max-w-7xl px-6 pb-2 pt-3 sm:px-6 lg:px-8">
            <div
                class="w-full bg-white p-6 text-gray-900 shadow-sm sm:rounded-lg dark:bg-gray-800 dark:text-gray-100"
            >
                ข้อมูลการขายตั้งแต่วันที่ {{ dates[1] }} ถึงวันที่
                {{ dates[dates.length - 1] }}
                <button @click="download(dates)">ดาวน์โหลด</button>
            </div>
        </div>
        <div
            class="mx-auto flex max-w-7xl space-x-3 px-6 pb-3 pt-1 sm:px-6 lg:px-8"
        >
            <div
                class="w-4/12 bg-white p-6 text-gray-900 shadow-sm sm:rounded-lg dark:bg-gray-800 dark:text-gray-100"
            >
                <div class="">ขายทั้งหมด</div>
                <span
                    class="text-xl font-semibold tracking-tight text-gray-900 sm:text-3xl"
                    >{{ number(glances.total_delta_sum) }}</span
                >
                ชิ้น
            </div>
            <div
                class="w-4/12 bg-white p-6 text-gray-900 shadow-sm sm:rounded-lg dark:bg-gray-800 dark:text-gray-100"
            >
                <div class="">ขายเฉลี่ย/วัน</div>
                <span
                    class="text-xl font-semibold tracking-tight text-gray-900 sm:text-3xl"
                    >{{ number(glances.avg_delta) }}</span
                >
                ชิ้น
            </div>
            <div
                class="w-4/12 bg-white p-6 text-gray-900 shadow-sm sm:rounded-lg dark:bg-gray-800 dark:text-gray-100"
            >
                <div class="">
                    ขายสูงสุด
                    <span class="text-xs"
                        >(วันที่ {{ glances.max_delta[0] }})</span
                    >
                </div>
                <span
                    class="text-xl font-semibold tracking-tight text-gray-900 sm:text-3xl"
                    >{{ number(glances.max_delta[1]) }}</span
                >
                ชิ้น
            </div>
        </div>
        <div class="">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <span
                            v-for="(sku, index) in sku_list"
                            :key="'sku_' + index"
                            class="mb-1 mr-1 inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10"
                        >
                            {{ sku }}
                        </span>
                    </div>
                    <apexchart
                        width="100%"
                        type="line"
                        :options="options"
                        :series="series"
                    ></apexchart>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
