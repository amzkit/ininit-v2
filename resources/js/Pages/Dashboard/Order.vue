<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// 📌 Chart Data
const series = ref([]);
const selected_data = ref([]);
const comparison_data = ref([]);
const date_comparison_between = ref({});

// 📌 Default Selected Month & Year
const now = new Date();
const currentYear = now.getFullYear();
const currentMonth = String(now.getMonth() + 1).padStart(2, "0");

// 📌 Dropdown Data for Months & Years
const months = computed(() => {
    return Array.from({ length: 12 }, (_, i) => {
        const month = String(i + 1).padStart(2, "0");
        return {
            value: month,
            label: new Date(`${currentYear}-${month}-01`).toLocaleString("default", {
                month: "long",
            }),
        };
    });
});

const years = computed(() => {
    const startYear = currentYear - 5;
    const endYear = currentYear;
    return Array.from({ length: endYear - startYear + 1 }, (_, i) => startYear + i);
});

const selectedMonth = ref(currentMonth);
const selectedYear = ref(currentYear);

// 📌 Get Month Label (Ensures selectedMonthLabel and comparisonMonthLabel exist)
const getMonthName = (type) => {
    if (!date_comparison_between.value[type]) return "";
    const date = new Date(date_comparison_between.value[type]);
    return date.toLocaleString("default", { month: "long", year: "numeric" });
};

const selectedMonthLabel = computed(() => getMonthName("selected_date"));
const comparisonMonthLabel = computed(() => getMonthName("comparison_date"));

// 📌 Chart Options
const options = ref({
    chart: {
        id: 'vuechart-example',
        events: {
            markerClick: (event, chartContext, { dataPointIndex }) => {
                chartContext.toggleDataPointSelection(dataPointIndex);
            }
        },
    },
    xaxis: {
        type: "datetime",
    },
    stroke: {
        width: [2, 1],
        dashArray: [0, 5]
    },
    dataLabels: {
        enabled: true,
        formatter: (value, { seriesIndex, dataPointIndex, w }) => {
            if (seriesIndex !== 0) return ""; // Hide labels for comparison data

            const currentValue = w.globals.series[0][dataPointIndex];
            const prevValue =
                dataPointIndex > 0 ? w.globals.series[0][dataPointIndex - 1] : null;

            if (prevValue !== null && Math.abs(currentValue - prevValue) > 500 && currentValue !== 0) {
                return value;
            }
            return "";
        },
        style: {
            fontSize: "12px",
            colors: ["#000"]
        },
        offsetY: -10
    },
    plotOptions: {
        line: {
            zIndex: 1,
        }
    },
    tooltip: {
        shared: true,
        x: {
            format: "ddd dd, MMM",
        },
        y: {
            formatter: (value) => value.toLocaleString()
        },
        custom: ({ series, seriesIndex, dataPointIndex, w }) => {
            const selectedDate = new Date(w.globals.seriesX[0][dataPointIndex]).toLocaleDateString("en-US", {
                weekday: "short",
                day: "2-digit",
                month: "short"
            });

            const comparisonDate = comparison_data.value[dataPointIndex]?.[2] ?? "N/A";
            const comparisonValue = series[1]?.[dataPointIndex] ?? 0;

            return `<div style="padding: 10px;">
                        <strong>${selectedDate}</strong><br/>
                        <span style="color:#FF5733">● ${selectedMonthLabel.value}: ${series[0][dataPointIndex]}</span><br/>
                        <span style="color:#3773f5">● ${comparisonMonthLabel.value} (${comparisonDate}): ${comparisonValue}</span>
                    </div>`;
        }
    },
    markers: {
        size: 3,
        strokeWidth: 1,
        hover: {
            sizeOffset: 2
        }
    }
});

const selected_analytics = ref({});
const comparison_analytics = ref({});

const fetchData = async () => {
    await axios.get('/api/dashboard/order/month', {
        params: {
            month: selectedMonth.value,
            year: selectedYear.value,
        }
    }).then((response) => {
        if (response.data.success === true) {
            selected_data.value = response.data.packing_data.selected_data;
            comparison_data.value = response.data.packing_data.comparison_data;
            date_comparison_between.value = response.data.packing_data.date_comparison_between;
            selected_analytics.value = response.data.packing_data.selected_analytics;
            comparison_analytics.value = response.data.packing_data.comparison_analytics;

            series.value = [];
            addChart("selected_data", selected_data.value);
            addChart("comparison_data", comparison_data.value);
        }
    });
};


// 📌 Add Data to Chart
const addChart = (type, chartData) => {
    if (chartData && chartData.length > 0) {
        series.value.push({
            name: type === "selected_data" ? selectedMonthLabel.value : comparisonMonthLabel.value, // ✅ Fix: Correctly set legend name
            data: chartData.map(d => ({ x: new Date(d[0]), y: d[1] })) // Ensure correct format
        });
    } else {
        console.error(`Data for ${type} not found!`);
    }
};

// 📌 Format Number
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

// 📌 Fetch Data on Page Load
onMounted(fetchData);

const formatNumber = (num) => {
    return num ? num.toLocaleString() : "0";
};

const formatThaiDate = (dateString) => {
    if (!dateString || dateString === "N/A") return "N/A"; // Handle empty values

    // ✅ Split the string into parts (e.g., "Mon 03, Feb")
    const parts = dateString.split(" ");
    if (parts.length < 3) return "N/A"; // Ensure correct format

    const day = parseInt(parts[1], 10); // Extract day
    const monthEng = parts[2].replace(",", ""); // Remove the comma from "Feb,"
    
    // ✅ Convert English month to Thai month
    const months = {
        "Jan": "ม.ค.", "Feb": "ก.พ.", "Mar": "มี.ค.", "Apr": "เม.ย.",
        "May": "พ.ค.", "Jun": "มิ.ย.", "Jul": "ก.ค.", "Aug": "ส.ค.",
        "Sep": "ก.ย.", "Oct": "ต.ค.", "Nov": "พ.ย.", "Dec": "ธ.ค."
    };

    const thaiMonth = months[monthEng] || monthEng; // Convert to Thai

    // ✅ Convert English weekday to Thai
    const weekdays = {
        "Sun": "อาทิตย์", "Mon": "จันทร์", "Tue": "อังคาร",
        "Wed": "พุธ", "Thu": "พฤหัส", "Fri": "ศุกร์", "Sat": "เสาร์"
    };

    const thaiWeekday = weekdays[parts[0]] || parts[0]; // Convert to Thai

    return `${thaiWeekday} ${day} ${thaiMonth}`;
};




</script>

<template>
    <Head title="Order" />

    <AuthenticatedLayout>
        <template #header>
            <div class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Order
            </div>
        </template>

        <!-- 📌 Month & Year Selector -->
        <div class="mt-2 mx-2">
            <div class="max-w-sm mx-auto p-3 bg-white rounded-lg shadow-md">
                <label class="block text-sm font-medium text-gray-700">ข้อมูลเดือน</label>

                <div class="flex gap-2 mt-2">
                    <select v-model="selectedMonth"
                        class="w-1/2 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2">
                        <option v-for="month in months" :key="month.value" :value="month.value">
                            {{ month.label }}
                        </option>
                    </select>

                    <select v-model="selectedYear"
                        class="w-1/2 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2">
                        <option v-for="year in years" :key="year" :value="year">
                            {{ year }}
                        </option>
                    </select>
                </div>

                <button @click="fetchData"
                    class="mt-4 w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-700 transition">
                    Submit
                </button>
            </div>
        </div>
        
        <!-- 📌 Analytics Section -->
        <!-- 📌 Minimalist Table Style with Thai Dates -->
        <div class="max-w-3xl mx-2 p-3 bg-white rounded-lg shadow-md mt-2">
            <h2 class="text-xl font-bold text-gray-700 mb-2">สถิติต่างๆ</h2>

            <table class="w-full border-collapse border border-gray-300 text-sm">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="p-3 border border-gray-300">Metric</th>
                        <th class="p-3 border border-gray-300">{{ selectedMonthLabel }}</th>
                        <th class="p-3 border border-gray-300">{{ comparisonMonthLabel }}</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr>
                        <td class="p-3 border border-gray-300">รวมทั้งเดือน</td>
                        <td class="p-3 border border-gray-300 font-semibold">{{ formatNumber(selected_analytics.sum) }}</td>
                        <td class="p-3 border border-gray-300">{{ formatNumber(comparison_analytics.sum) }}</td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-gray-300">วันที่สูงสุด</td>
                        <td class="p-3 border border-gray-300 font-semibold">
                            {{ formatNumber(selected_analytics.max_value) }} 
                            <br> <span class="text-xs text-gray-500">{{ formatThaiDate(selected_analytics.max_date) }}</span>
                        </td>
                        <td class="p-3 border border-gray-300">
                            {{ formatNumber(comparison_analytics.max_value) }} 
                            <br> <span class="text-xs text-gray-500">{{ formatThaiDate(comparison_analytics.max_date) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-gray-300">วันที่ต่ำสุด</td>
                        <td class="p-3 border border-gray-300 font-semibold">
                            {{ formatNumber(selected_analytics.min_value) }} 
                            <br> <span class="text-xs text-gray-500">{{ formatThaiDate(selected_analytics.min_date) }}</span>
                        </td>
                        <td class="p-3 border border-gray-300">
                            {{ formatNumber(comparison_analytics.min_value) }} 
                            <br> <span class="text-xs text-gray-500">{{ formatThaiDate(comparison_analytics.min_date) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-gray-300">เฉลี่ย</td>
                        <td class="p-3 border border-gray-300 font-semibold">{{ formatNumber(selected_analytics.average) }}</td>
                        <td class="p-3 border border-gray-300">{{ formatNumber(comparison_analytics.average) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>




        <!-- 📌 Chart -->
        <div class="flex justify-center w-full">
            <div class="w-full max-w-[800px] p-4">
                <apexchart class="w-full h-[250px] sm:h-[400px]" height="250px" type="line" :options="options"
                    :series="series"></apexchart>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
