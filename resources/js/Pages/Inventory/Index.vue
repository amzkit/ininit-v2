<template>
  <Head title="Inventory" />

  <AuthenticatedLayout>
    <div class="p-4 bg-gray-100 min-h-screen">
      <h1 class="text-2xl font-bold text-gray-800 mb-4">Inventory Management</h1>

      <!-- Date Picker -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
          <label class="block text-gray-700 font-semibold mr-2">Date:</label>
          <input 
            type="date" 
            v-model="selectedDate"
            @change="fetchInventory"
            class="border border-gray-300 rounded-lg py-1 px-2 focus:outline-none focus:border-blue-500 transition w-36"
          />
        </div>

        <button class="ml-auto bg-blue-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-600 transition"
          @click="notify">
          🔔 Notify
        </button>
      </div>

      <div class="my-2">
  <h2 class="text-lg font-semibold mb-2">สินค้าเข้า/ออก</h2>

  <div class="overflow-x-auto bg-white rounded-lg shadow-md">
    <table class="min-w-full border-collapse text-sm">
      <thead>
        <tr class="bg-blue-500 text-white text-left">
          <th class="py-2 px-2 font-semibold">SKU</th>
          <th class="py-2 px-2 font-semibold">ชนิด</th>
          <th class="py-2 px-2 font-semibold">จำนวน</th>
          <th class="py-2 px-2 font-semibold">ชื่อ</th>
          <th class="py-2 px-2 font-semibold">Note</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in inventory_ios" :key="item.id" class="border-b hover:bg-gray-50 transition">
          <td class="py-2 px-2">{{ item.product?.sku }}</td>
          <td class="py-2 px-2 font-semibold" :class="item.type === 'in' ? 'text-green-600' : 'text-red-600'">
            {{ item.type === 'in' ? 'เข้า' : 'ออก' }}
          </td>
          <td class="py-2 px-2 text-right">{{ item.io_amount }}</td>
          <td class="py-2 px-2">{{ item.partner?.name ?? '-' }}</td>
          <td class="py-2 px-2">{{ item.note ?? '-' }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

      <!-- Inventory List -->
      <div class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-blue-500 text-white text-left">
              <th class="py-2 px-2 font-semibold">SKU</th>
              <th class="py-2 px-2 font-semibold text-right">เดิม</th>
              <th class="py-2 px-2 font-semibold text-right">วันนี้</th>
              <th class="py-2 px-2 font-semibold text-center"></th>
            </tr>
          </thead>
          <draggable v-model="products" itemKey="id" @end="saveOrder" class="drag-area" tag="tbody">
            <template #item="{ element }">
              <tr :key="element.id" class="border-b hover:bg-gray-50 transition">
                <td class="py-1 px-1 text-gray-700">
                  <span class="cursor-move">☰</span> {{ element.sku }}
                </td>

                <td 
                  class="py-1 px-1 text-right text-gray-600 cursor-pointer hover:text-blue-500 transition"
                  title="Click to reset to yesterday"
                >
                  <span @click="resetToYesterday(element)">{{ element.yesterday_inventory ?? 0 }}</span> 
                </td>

                <td class="py-1 px-1 text-right">
                  <input 
                    v-model="element.selected_inventory"
                    type="number" 
                    class="border border-gray-300 rounded-lg py-1 px-2 text-right focus:outline-none focus:border-blue-500 transition w-full"
                    @change="saveInventory(element)"
                    :disabled="isLoading"
                  />
                </td>

                <td class="py-1 px-1 text-center">
                  <button 
                    @click="openInOutModal(element)"
                    class="bg-blue-500 text-white py-1 px-2 rounded-lg hover:bg-blue-600 transition"
                    title="In/Out"
                  >
                    +/-
                  </button>
                </td>
              </tr>
            </template>
          </draggable>
        </table>
      </div>

      <!-- In/Out Modal -->
      <transition name="fade">
  <div v-if="showInOutModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-xl font-bold mb-4 text-center">Inventory In/Out</h2>

      <!-- Select Inventory Type -->
      <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-1">ประเภท</label>
        <select 
          v-model="selectedProduct.type"
          class="border border-gray-300 rounded-lg py-2 px-4 w-full focus:outline-none focus:border-blue-500 transition"
          @change="updateNote"
        >
          <option value="in">สินค้าเข้า</option>
          <option value="out">สินค้าออก</option>
        </select>
      </div>

      <!-- SKU Display -->
      <p class="text-gray-700 mb-2 font-semibold">SKU: <span class="text-blue-600">{{ selectedProduct.sku }}</span></p>

      <!-- In/Out Amount -->
      <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-1">จำนวน</label>
        <input 
          v-model.number="selectedProduct.io_amount"
          type="number" 
          min="0"
          placeholder="Enter quantity"
          class="border border-gray-300 rounded-lg py-2 px-4 w-full focus:outline-none focus:border-blue-500 transition"
          @input="handleAmountInput"
          @focus="$event.target.select()"
        />
      </div>

      <!-- Partner Selection -->
      <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-1">ผู้รับ</label>
        
        <!-- Last 5 Used Partners -->
        <div class="flex flex-wrap gap-2 mb-2">
          <button 
            v-for="partner in partners"
            :key="partner.id"
            class="px-3 py-1 border rounded-lg text-sm hover:bg-gray-200"
            @click="selectPartner(partner)">
            {{ partner.name }}
          </button>
        </div>

        <!-- Search Input -->
        <div class="flex items-center justify-between mb-4 gap-2">
          <input 
            v-model="partnerSearch"
            type="text"
            placeholder="ค้นหาผู้รับ"
            class="border border-gray-300 rounded-lg py-2 px-4 w-full focus:outline-none focus:border-blue-500 transition"
            @input="fetchPartners"
          />
          <button 
          @click="showNewPartnerForm = !showNewPartnerForm"
          class="bg-blue-400 text-white py-2 px-4 rounded-lg hover:bg-blue-500 transition">
          เพิ่ม
        </button>
        </div>
      </div>

      <!-- Add New Partner Button -->


      <!-- New Partner Form (Hidden Until Clicked) -->
      <div v-if="showNewPartnerForm" class="border p-4 rounded-lg bg-gray-50">
        <label class="block text-gray-700 font-semibold mb-1">เพิ่มผู้รับใหม่</label>
        <input 
          v-model="newPartner.name"
          type="text"
          placeholder="ชื่อผู้รับ"
          class="border border-gray-300 rounded-lg py-2 px-4 w-full mb-2 focus:outline-none focus:border-blue-500 transition"
        />
        <button 
          @click="createNewPartner"
          class="bg-green-500 text-white py-1 px-4 rounded-lg hover:bg-green-600 transition w-full">
          เพิ่ม
        </button>
      </div>

      <!-- Note Input (Auto-filled but Editable) -->
      <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-1">Note</label>
        <input 
          v-model="selectedProduct.note"
          type="text"
          class="border border-gray-300 rounded-lg py-2 px-4 w-full focus:outline-none focus:border-blue-500 transition"
        />
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-between mt-6">
        <button 
          @click="closeModal"
          class="bg-gray-400 text-white py-2 px-4 rounded-lg hover:bg-gray-500 transition">
          ยกเลิก
        </button>
        <button 
          @click="saveInOut"
          class="py-2 px-4 rounded-lg transition"
          :class="selectedProduct.io_amount ? 'bg-blue-500 text-white hover:bg-blue-600' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
          :disabled="!selectedProduct.io_amount">
          ยืนยัน
        </button>
      </div>
    </div>
  </div>
</transition>





    </div>

  </AuthenticatedLayout>
  <transition name="fade">
  <div 
    v-if="showSuccessMessage" 
    class="fixed bottom-4 right-4 bg-green-500 text-white py-2 px-4 rounded-lg shadow-lg">
    {{ successMessage }}
  </div>
</transition>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import debounce from "lodash/debounce";
import draggable from "vuedraggable";

// Data Variables
const products = ref([]);
const bangkokOffset = 7 * 60 * 60 * 1000; // GMT+7 in milliseconds
const selectedDate = ref(new Date(Date.now() + bangkokOffset).toISOString().substr(0, 10));

//const selectedDate = ref(new Date().toISOString().substr(0, 10));


const showSuccessMessage = ref(false);
const successMessage = ref("");

const showInOutModal = ref(false);
const partnerSearch = ref("");
const partners = ref([]);
const newPartner = ref({ name: "", mobile_number: "" });
const showNewPartnerForm = ref(false);
const isLoading = ref(false);
const selectedProduct = ref({
    sku: "",
    io_amount: 1, // ✅ Default to positive value
    type: "in",   // ✅ Default to "in"
    partner_id: "",
    note: ""
});

const inventory_ios = ref([]);

// Fetch Inventory
const fetchInventory = async () => {
  console.log(selectedDate.value)
  try {
    const response = await axios.get("/api/inventory", { params: { date: selectedDate.value } });
    products.value = response.data.products;
    inventory_ios.value = response.data.inventory_ios;
  } catch (error) {
    console.error("Error fetching inventory:", error);
  }
};

// ✅ Open Modal with Default Values
const openInOutModal = (product) => {
  selectedProduct.value = { 
    ...product, 
    type: "in", 
    io_amount: 0, 
    note: "" 
  };
  showInOutModal.value = true;
  fetchLastUsedPartners();
};

// Close Modal
const closeModal = () => {
  showInOutModal.value = false;
};


// ✅ Fetch Last 5 Used Partners
const fetchLastUsedPartners = async () => {
  try {
    const response = await axios.get("/api/partner/last-used");
    partners.value = response.data.partners; // Assume API returns last 5 partners
  } catch (error) {
    console.error("Error fetching last used partners:", error);
  }
};

// ✅ Select Partner from Last Used
const selectPartner = (partner) => {
  selectedProduct.value.partner_id = partner.id;
  updateNote();
};

// Search Partners (Debounced)
const fetchPartners = debounce(async () => {
  if (!partnerSearch.value.trim()) {
    partners.value = [];
    return;
  }
  try {
    const response = await axios.get("/api/partner/search", { params: { query: partnerSearch.value } });
    partners.value = response.data;
  } catch (error) {
    console.error("Error fetching partners:", error);
  }
}, 300);

// ✅ Automatically Adjust Note Based on Selection
const updateNote = () => {
  const partner = partners.value.find(p => p.id === selectedProduct.value.partner_id);
  const partnerName = partner ? partner.name : "-";
  selectedProduct.value.note = selectedProduct.value.type === "in" 
    ? `เข้าจาก ${partnerName}` 
    : `ส่งไป ${partnerName}`;
};

// ✅ Handle Input to Auto-Set Type
const handleAmountInput = () => {
  if (selectedProduct.value.io_amount < 0) {
    selectedProduct.value.io_amount = Math.abs(selectedProduct.value.io_amount); // Convert to positive
    selectedProduct.value.type = "out"; // Auto-switch to "out"
    updateNote();
  }
};

// ✅ Save In/Out Transaction
const saveInOut = async () => {
  try {
    await axios.post("/api/inventory/in_out", {
      product_id: selectedProduct.value.id,
      type: selectedProduct.value.type,
      io_amount: selectedProduct.value.io_amount,
      partner_id: selectedProduct.value.partner_id || null,
      note: selectedProduct.value.note,
      date: selectedDate.value,
    });

    inventory_ios.value.push({
      product: { sku: selectedProduct.value.sku },
      type: selectedProduct.value.type,
      io_amount: selectedProduct.value.io_amount,
      partner: partners.value.find(p => p.id === selectedProduct.value.partner_id),
      note: selectedProduct.value.note
    });

    // ✅ Show success toast message
    showToast("Inventory In/Out updated successfully!");
    closeModal();
  } catch (error) {
    console.error("Error saving inventory IO:", error);
  }
};

// Create New Partner
const createNewPartner = async () => {
  try {
    await axios.post("/api/partner/create", { name: newPartner.value.name, mobile_number: newPartner.value.mobile_number || null });
    newPartner.value = { name: "", mobile_number: "" };
    showNewPartnerForm.value = false;
    fetchPartners();
  } catch (error) {
    console.error("Error creating new partner:", error);
  }
};

const saveOrder = async () => {
  try {
    const orderedIds = products.value.map(product => product.id);
    await axios.post("/api/inventory/order", { ordered_ids: orderedIds });
  } catch (error) {
    console.error("Error saving order:", error);
  }
};

const saveInventory = async (product) => {
  isLoading.value = true;
  try {
    await axios.post("/api/inventory/update", {
      product_id: product.id,
      inventory: product.selected_inventory,
      date: selectedDate.value,
    });

    showToast("Inventory updated successfully!");
  } catch (error) {
    console.error("Error updating inventory:", error);
  } finally {
    isLoading.value = false;
  }
};

const resetToYesterday = (product) => {
  product.selected_inventory = product.yesterday_inventory;
  saveInventory(product);
};

const notify = async () => {
  try {
    const response = await axios.get("/api/notification/inventory", {
      params: { date: selectedDate.value } // ✅ Send selected date
    });

    if (response.data.success) {
      showToast(`Notification sent for ${selectedDate.value}!`);
    } else {
      alert("Notification failed.");
    }
  } catch (error) {
    console.error("Error triggering notification:", error);
    alert("Failed to send notification.");
  }
};


const showToast = (message) => {
  successMessage.value = message;
  showSuccessMessage.value = true;
  
  setTimeout(() => {
    showSuccessMessage.value = false;
  }, 2000);
};
// Watch for Search Changes
watch(partnerSearch, fetchPartners);

onMounted(fetchInventory);
</script>
