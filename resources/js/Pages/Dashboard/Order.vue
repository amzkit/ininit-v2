<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import axios from 'axios';

// 📌 Data for Selected and Comparison Charts
const selectedChartData = ref([]);
const comparisonChartData = ref([]);
const comparisonDates = ref({});
const selectedAnalytics = ref({});
const comparisonAnalytics = ref({});
const dayOfWeekAnalytics = ref({});  // Data for Day of Week Analytics
const periodAnalytics = ref({});  // Data for Period Analytics

// 📌 Default Date Information (Month and Year)
const currentDate = new Date();
const currentYear = currentDate.getFullYear();
const currentMonth = String(currentDate.getMonth() + 1).padStart(2, "0");

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

// 📌 Date Labels for Comparison
const getMonthName = (type) => {
  if (!comparisonDates.value[type]) return "";
  const date = new Date(comparisonDates.value[type]);
  return date.toLocaleString("default", { month: "long", year: "numeric" });
};

const selectedMonthLabel = computed(() => getMonthName("selected_date"));
const comparisonMonthLabel = computed(() => getMonthName("comparison_date"));

// 📌 Chart Options and Series for Line Chart
const lineChartOptions = ref({
  chart: {
    id: 'line-chart',
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
      if (seriesIndex !== 0) return "";
      const currentValue = w.globals.series[0][dataPointIndex];
      const prevValue = dataPointIndex > 0 ? w.globals.series[0][dataPointIndex - 1] : null;
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
    x: { format: "ddd dd, MMM" },
    y: { formatter: (value) => value.toLocaleString() },
    custom: ({ series, seriesIndex, dataPointIndex, w }) => {
      const selectedDate = new Date(w.globals.seriesX[0][dataPointIndex]).toLocaleDateString("en-US", {
        weekday: "short",
        day: "2-digit",
        month: "short"
      });
      const comparisonDate = comparisonChartData.value[dataPointIndex]?.[2] ?? "N/A";
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
    hover: { sizeOffset: 2 }
  }
});

const lineChartSeries = ref([]);
// 📌 Bar Chart Options and Series
const barChartWeekDayOptions = ref({
  title: { text:''},
  chart: {
    id: "day-of-week-analytics-bar-chart",
    type: "bar",
    stacked: false,
    background: "#FFFFFF"
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: "45%",
      endingShape: "rounded",
      dataLabels: { position: 'top' },
    },
  },
  xaxis: {
    categories: ["Sun", "Mon", "Tue", "Wed", "Thur", "Fri", "Sat"],
    title: {
        text: "Order by Weekdays",
        style: {
          fontSize: "14px",
          fontWeight: "bold"
        }
      }
  },
  yaxis: {
    title: { text: "Orders" },
  },
  fill: { opacity: [1, 0.2] },
  legend: {
    position: "bottom",
    horizontalAlign: "center",
  },
  dataLabels: {
    enabled: true,
    style: { fontSize: "12px", colors: ["#000"] },
    position: 'top',
    floating: true,
    offsetY: -20,
  }
});

const barChartWeekDaySeries = ref([[],[]]);  // Data for Bar Chart
const barChartPeriodOptions = ref({
  chart: {
    id: "day-of-week-analytics-bar-chart",
    type: "bar",
    stacked: false,
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: "45%",
      endingShape: "rounded",
      dataLabels: { position: 'top' },
    },
  },
  xaxis: {
    categories: ["Day 1-10", "Day 11-20", "Day 21-30(31)"],
  },
  yaxis: {
    title: { text: "Orders" },
    labels: {
      formatter: function (value) {
        return Math.floor(value); // Remove decimal points by rounding down
      },
    },
  },
  fill: { opacity: [1, 0.1] },
  legend: {
    position: "bottom",
    horizontalAlign: "center",
  },
  dataLabels: {
    enabled: true,
    style: { fontSize: "12px", colors: ["#000"] },
    position: 'top',
    floating: true,
    offsetY: -20,
    formatter: function (value) {
      return Math.floor(value); // Remove decimal points in data labels
    },
  }
});

// 📌 Period Selector
const selectedPeriods = ref(6);
const availablePeriods = ref([3, 4, 6]);

const barChartPeriodSeries = ref([[]]);  // Data for Bar Chart


// 📌 Chart Options and Series for Period Analytics
const periodChartOptions = ref({});
const periodChartSeries = ref([]);

// 📌 Normalize Period Data with Descriptive X-Axis Titles
const normalizePeriodData = (periods) => {
  const selectedData = periodAnalytics.value?.[periods]?.selected || {};
  const comparisonData = periodAnalytics.value?.[periods]?.comparison || {};

  // 📌 Extract Descriptive Categories Directly from Backend Labels
  const categories = Object.keys(selectedData.average || {});

  // 📌 Extract Average Data for Selected and Comparison
  const averageSelected = categories.map(label => selectedData.average[label] || 0);
  const averageComparison = categories.map(label => comparisonData.average[label] || 0);

  return {
    series: [
      {
        name: selectedMonthLabel.value,
        data: averageSelected
      },
      {
        name: comparisonMonthLabel.value,
        data: averageComparison
      }
    ],
    categories
  };
};

// 📌 Update Chart Based on Selected Periods
const updatePeriodChart = () => {
  const data = normalizePeriodData(selectedPeriods.value);
  periodChartSeries.value = data.series;
  periodChartOptions.value = {
    chart: {
      type: "bar",
      toolbar: { show: false }
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: "45%",
        endingShape: "rounded",
        dataLabels: { position: 'top' },
      },
    },
    xaxis: {
      categories: data.categories,
      title: {
        text: selectedPeriods.value + " Periods",
        style: {
          fontSize: "14px",
          fontWeight: "bold"
        }
      }
    },
    yaxis: {
      labels: {
        formatter: (value) => value.toFixed(2)
      }
    },
    tooltip: {
      y: {
        formatter: (value) => value.toFixed(2)
      }
    },
    dataLabels: {
      enabled: true,
      style: { fontSize: "12px", colors: ["#000"] },
      position: 'top',
      floating: true,
      offsetY: -20,
    }
  };
};


// 📌 Fetch Data
const fetchData = async () => {
  await axios.get('/api/dashboard/order/month', {
    params: {
      month: selectedMonth.value,
      year: selectedYear.value,
    }
  }).then((response) => {
    if (response.data.success === true) {
        selectedChartData.value = response.data.packing_data.selected_data;
        comparisonChartData.value = response.data.packing_data.comparison_data;
        comparisonDates.value = response.data.analytics_data.date_comparison_between;
        selectedAnalytics.value = response.data.analytics_data.selected_analytics;
        comparisonAnalytics.value = response.data.analytics_data.comparison_analytics;
        dayOfWeekAnalytics.value = response.data.analytics_data.day_of_week_analytics;
        periodAnalytics.value = response.data.analytics_data.period_analytics;

        // Line Chart Data
        lineChartSeries.value = [
            { name: selectedMonthLabel.value, data: selectedChartData.value },
            { name: comparisonMonthLabel.value, data: comparisonChartData.value }
        ]

        // Prepare Bar Chart Data (Average by Day)
        barChartWeekDaySeries.value = []
        const daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

        // Average
        const selectedAverageData = daysOfWeek.map(day => dayOfWeekAnalytics.value.selected.average[day] || 0);
        const comparisonAverageData = daysOfWeek.map(day => dayOfWeekAnalytics.value.comparison.average[day] || 0);
        console.log(barChartWeekDayOptions.value)
        barChartWeekDaySeries.value.push([
            { name: selectedMonthLabel.value, data: selectedAverageData },
            { name: comparisonMonthLabel.value, data: comparisonAverageData},
        ]);

        // Max
        const selectedMaxData = daysOfWeek.map(day => dayOfWeekAnalytics.value.selected.max[day] || 0);
        const comparisonMaxData = daysOfWeek.map(day => dayOfWeekAnalytics.value.comparison.max[day] || 0);
        barChartWeekDaySeries.value.push([
            { name: selectedMonthLabel.value, data: selectedMaxData },
            { name: comparisonMonthLabel.value, data: comparisonMaxData},
        ]);

        // Period
        periodAnalytics.value = response.data.analytics_data.period_analytics;

        // 📌 Update Period Chart
        updatePeriodChart();
        //barChartPeriodSeries.value = []
        //barChartPeriodSeries.value.push([
        //    { name: selectedMonthLabel.value, data: selectedPeriodData },
        //    { name: comparisonMonthLabel.value, data: comparisonPeriodData },
        //]);

    }
  });
};


// 📌 Helper Methods (Formatting)
const formatNumber = (num) => num ? num.toLocaleString() : "0";
const formatThaiDate = (dateString) => {
  if (!dateString || dateString === "N/A") return "N/A";
  const parts = dateString.split(" ");
  if (parts.length < 3) return "N/A";
  const day = parseInt(parts[1], 10);
  const monthEng = parts[2].replace(",", "");
  const months = { "Jan": "ม.ค.", "Feb": "ก.พ.", "Mar": "มี.ค.", "Apr": "เม.ย.", "May": "พ.ค.", "Jun": "มิ.ย.", "Jul": "ก.ค.", "Aug": "ส.ค.", "Sep": "ก.ย.", "Oct": "ต.ค.", "Nov": "พ.ย.", "Dec": "ธ.ค." };
  const thaiMonth = months[monthEng] || monthEng;
  const weekdays = { "Sun": "อาทิตย์", "Mon": "จันทร์", "Tue": "อังคาร", "Wed": "พุธ", "Thu": "พฤหัส", "Fri": "ศุกร์", "Sat": "เสาร์" };
  const thaiWeekday = weekdays[parts[0]] || parts[0];
  return `${thaiWeekday} ${day} ${thaiMonth}`;
};



const isSticky = ref(false);

// 📌 Scroll Event Listener
const handleScroll = () => {
    const navbarHeight = 64; // Approximate height of the navbar (h-16)
    isSticky.value = window.scrollY > navbarHeight;
};

onMounted(() => {
  // 📌 Fetch Data on Page Load
  fetchData();
  window.addEventListener('scroll', handleScroll);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});

const goToPreviousMonth = () => {
  let month = parseInt(selectedMonth.value);
  let year = parseInt(selectedYear.value);

  // 📌 Go to the previous month
  if (month === 1) {
    month = 12;
    year -= 1;
  } else {
    month -= 1;
  }

  // 📌 Update the values
  selectedMonth.value = String(month).padStart(2, "0");
  selectedYear.value = String(year);
  fetchData();
}

// 📌 Next Month Visibility
const isNextMonthAvailable = ref(false);

// 📌 Check if Next Month is Available
const checkNextMonthAvailability = () => {
  let month = parseInt(selectedMonth.value);
  let year = parseInt(selectedYear.value);

  // 📌 Go to the next month
  if (month === 12) {
    month = 1;
    year += 1;
  } else {
    month += 1;
  }

  // 📌 Check if Next Month is within the selectable range
  isNextMonthAvailable.value = years.value.includes(year) && months.value.some(m => m.value === String(month).padStart(2, "0"));
};

// 📌 Go to Next Month
const goToNextMonth = () => {
  if (!isNextMonthAvailable.value) return;
  let month = parseInt(selectedMonth.value);
  let year = parseInt(selectedYear.value);

  // 📌 Go to the next month
  if (month === 12) {
    month = 1;
    year += 1;
  } else {
    month += 1;
  }

  // 📌 Update the values
  selectedMonth.value = String(month).padStart(2, "0");
  selectedYear.value = String(year);

  // 📌 Re-check availability
  checkNextMonthAvailability();
  fetchData();
};

// 📌 Watcher to Update Next Month Availability
watch([selectedMonth, selectedYear], () => {
  checkNextMonthAvailability();
});
</script>

<template>
  <Head title="Order" />

  <AuthenticatedLayout>


    <!-- 📌 Month & Year Selector -->
    <!-- 📌 Floating Date Selector Below Top Bar -->
    <!-- 📌 Sticky Date Selector -->
    <div
        :class="{
            'fixed top-0 left-0 w-full z-20 bg-white shadow-md border-b border-gray-200 transition-transform duration-300 transform': true,
            '-translate-y-full': !isSticky,
            'translate-y-0': isSticky
        }"
    >
        <div class="max-w-4xl mx-auto p-3 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <!-- 📌 Date Selection (One Row on Mobile) -->
            <div class="flex flex-col w-full md:flex-row md:items-center gap-2">
                <label class="block text-sm font-medium text-gray-700">ข้อมูลเดือน:</label>
                <div class="flex w-full gap-2">
                    <!-- 📌 Action Buttons (Compact Previous Month Icon) -->
                    <div class="flex items-center gap-2 mt-2 md:mt-0">
                        <!-- Previous Month Icon -->
                        <button
                            @click="goToPreviousMonth"
                            class="text-gray-500 hover:text-gray-700 transition rounded-full p-2 hover:bg-gray-100 focus:outline-none"
                            title="Previous Month"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>
                    <select v-model="selectedMonth" class="w-1/2 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2" @change="fetchData">
                        <option v-for="month in months" :key="month.value" :value="month.value">
                            {{ month.label }}
                        </option>
                    </select>
                    <select v-model="selectedYear" class="w-1/2 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2" @change="fetchData">
                        <option v-for="year in years" :key="year" :value="year">
                            {{ year }}
                        </option>
                    </select>
                <!-- 📌 Next Month Icon (Shown Only When Available) -->
                <button 
                    v-if="isNextMonthAvailable"
                    @click="goToNextMonth"
                    class="text-gray-500 hover:text-gray-700 transition rounded-full p-2 hover:bg-gray-100 focus:outline-none"
                    title="Next Month"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                </div>
            </div>


        </div>
    </div>

    <!-- 📌 Analytics Table -->
    <div class="max-w-3xl mx-auto p-3 bg-white rounded-lg shadow-md mt-2">
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
            <td class="p-3 border border-gray-300 font-semibold">{{ formatNumber(selectedAnalytics.sum) }}</td>
            <td class="p-3 border border-gray-300">{{ formatNumber(comparisonAnalytics.sum) }}</td>
          </tr>
          <tr>
            <td class="p-3 border border-gray-300">วันที่สูงสุด</td>
            <td class="p-3 border border-gray-300 font-semibold">{{ formatNumber(selectedAnalytics.max_value) }}
              <br> <span class="text-xs text-gray-500">{{ formatThaiDate(selectedAnalytics.max_date) }}</span>
            </td>
            <td class="p-3 border border-gray-300">{{ formatNumber(comparisonAnalytics.max_value) }}
              <br> <span class="text-xs text-gray-500">{{ formatThaiDate(comparisonAnalytics.max_date) }}</span>
            </td>
          </tr>
          <tr>
            <td class="p-3 border border-gray-300">วันที่ต่ำสุด</td>
            <td class="p-3 border border-gray-300 font-semibold">{{ formatNumber(selectedAnalytics.min_value) }}
              <br> <span class="text-xs text-gray-500">{{ formatThaiDate(selectedAnalytics.min_date) }}</span>
            </td>
            <td class="p-3 border border-gray-300">{{ formatNumber(comparisonAnalytics.min_value) }}
              <br> <span class="text-xs text-gray-500">{{ formatThaiDate(comparisonAnalytics.min_date) }}</span>
            </td>
          </tr>
          <tr>
            <td class="p-3 border border-gray-300">เฉลี่ย</td>
            <td class="p-3 border border-gray-300 font-semibold">{{ formatNumber(selectedAnalytics.average) }}</td>
            <td class="p-3 border border-gray-300">{{ formatNumber(comparisonAnalytics.average) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 📌 Line Chart -->
    <div class="flex justify-center max-w-3xl mx-auto my-2">
      <div class="w-full max-w-[800px] p-4 bg-white rounded-lg shadow-md">
        Order by Day in Month
        <apexchart class="w-full h-[250px] sm:h-[400px]" height="250px" type="line" :options="lineChartOptions" :series="lineChartSeries"></apexchart>
      </div>
    </div>

    <!-- 📌 Bar Chart -->
    <div class="flex justify-center max-w-3xl mx-auto my-2">
      <div class="w-full max-w-[800px] p-4 bg-white rounded-lg shadow-md">
        Average Order by Day of Week
        <apexchart class="w-full h-[350px]" type="bar" :options="barChartWeekDayOptions" :series="barChartWeekDaySeries[0]"></apexchart>
      </div>
    </div>

        <!-- 📌 Bar Chart -->
        <div class="flex justify-center max-w-3xl mx-auto my-2">
      <div class="w-full max-w-[800px] p-4 bg-white rounded-lg shadow-md">
        Max Order by Day of Week
        <apexchart class="w-full h-[350px]" type="bar" :options="barChartWeekDayOptions" :series="barChartWeekDaySeries[1]"></apexchart>
      </div>
    </div>

    <div class="flex justify-center max-w-3xl mx-auto my-2">
      <div class="w-full max-w-[800px] p-4 bg-white rounded-lg shadow-md">
        <!-- 📌 Flex Row for Title and Select Option -->
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-700">Average Order by Period</h3>
          <div class="flex items-center gap-2">
            <label for="period-select" class="text-sm text-gray-600">Select Periods:</label>
            <select 
              id="period-select"
              v-model="selectedPeriods" 
              @change="updatePeriodChart" 
              class="rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 pr-10">
              <option v-for="period in availablePeriods" :key="period" :value="period">
                {{ period }}
              </option>
            </select>
          </div>
        </div>
        <!-- 📌 ApexChart for Period Analytics -->
        <apexchart class="w-full h-[350px]" type="bar" :options="periodChartOptions" :series="periodChartSeries"></apexchart>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
