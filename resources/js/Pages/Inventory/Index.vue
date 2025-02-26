<template>
  <Head title="Inventory" />

  <AuthenticatedLayout>
  <div class="p-4 bg-gray-100 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Inventory Management</h1>

    <!-- Date Picker -->
    <div class="flex items-center mb-4">
      <label class="block text-gray-700 font-semibold mr-2">Date:</label>
      <input 
        type="date" 
        v-model="selectedDate"
        @change="fetchInventory"
        class="border border-gray-300 rounded-lg py-1 px-2 focus:outline-none focus:border-blue-500 transition w-36"
      />
    </div>

    <!-- Inventory List -->
    <div class="overflow-x-auto bg-white rounded-lg shadow-md">
      <table class="min-w-full border-collapse text-sm">
        <thead>
          <tr class="bg-blue-500 text-white text-left">
            <th class="py-2 px-2 font-semibold">SKU</th>
            <th class="py-2 px-2 font-semibold text-right">Yesterday</th>
            <th class="py-2 px-2 font-semibold text-right">Selected Date</th>
            <th class="py-2 px-2 font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <draggable 
            v-model="products"
            itemKey="id"
            @end="saveOrder"
            class="drag-area"
            tag="tbody"
          >
            <template #item="{ element }">
              <tr 
                :key="element.id" 
                class="border-b hover:bg-gray-50 transition"
              >
                <!-- SKU Column -->
                <td class="py-2 px-2 text-gray-700">
                  <span class="cursor-move">☰</span> 
                  {{ element.sku }}
                </td>

                <!-- Yesterday Column (Clickable for Reset) -->
                <td 
                  class="py-2 px-2 text-right text-gray-600 cursor-pointer hover:text-blue-500 transition"
                  @click="resetToYesterday(element)"
                  title="Click to reset to yesterday"
                >
                  {{ element.yesterday_inventory ?? 0 }}
                </td>

                <!-- Selected Date Column -->
                <td class="py-2 px-2 text-right">
                  <input 
                    v-model="element.selected_inventory"
                    type="number" 
                    class="border border-gray-300 rounded-lg py-1 px-2 text-right focus:outline-none focus:border-blue-500 transition w-full"
                    @change="saveInventory(element)"
                    :disabled="isLoading"
                  />
                </td>

                <!-- Actions Column -->
                <td class="py-2 px-2 text-center">
                  <button 
                    @click="openInOutModal(element)"
                    class="bg-blue-500 text-white py-1 px-2 rounded-lg hover:bg-blue-600 transition"
                    title="In/Out"
                  >
                    In/Out
                  </button>
                </td>
              </tr>
            </template>
          </draggable>
        </tbody>
      </table>
    </div>

    <!-- Success Notification -->
    <transition name="fade">
      <div 
        v-if="showSuccessMessage" 
        class="fixed bottom-4 right-4 bg-green-500 text-white py-2 px-4 rounded-lg shadow-lg"
      >
        Inventory updated successfully!
      </div>
    </transition>


<!-- In/Out Modal -->
<transition name="fade">
  <div 
    v-if="showInOutModal" 
    class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center"
  >
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-xl font-bold mb-4">Inventory In/Out</h2>
      <p class="text-gray-700 mb-2">SKU: {{ selectedProduct.sku }}</p>

      <!-- In/Out Value -->
      <input 
        v-model="selectedProduct.in_out"
        type="number" 
        placeholder="Enter In/Out Value" 
        class="border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:border-blue-500 transition w-full mb-4"
      />

      <!-- Search and Select Customer -->
      <input 
        v-model="customerSearch" 
        type="text" 
        placeholder="Search Customer" 
        class="border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:border-blue-500 transition w-full mb-2"
      />
      <select 
        v-model="selectedProduct.from_customer_id" 
        class="border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:border-blue-500 transition w-full mb-4"
      >
        <option value="">Select Customer</option>
        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
          {{ customer.name }}
        </option>
      </select>

      <!-- Create New Customer -->
      <button 
        @click="showNewCustomerForm = !showNewCustomerForm"
        class="text-blue-500 hover:text-blue-600 transition mb-4"
      >
        Add New Customer
      </button>
      <div v-if="showNewCustomerForm">
        <input 
          v-model="newCustomer.name"
          type="text" 
          placeholder="Customer Name" 
          class="border border-gray-300 rounded-lg py-2 px-4 w-full mb-2"
        />
        <button 
          @click="createNewCustomer"
          class="bg-blue-500 text-white py-1 px-4 rounded-lg hover:bg-blue-600 transition"
        >
          Save Customer
        </button>
      </div>
    </div>
  </div>
</transition>
  </div>
</AuthenticatedLayout>

</template>


<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import debounce from 'lodash/debounce'  // Debounce to optimize search input
import draggable from 'vuedraggable'

// Data Variables
const products = ref([])
const selectedDate = ref(new Date().toISOString().substr(0, 10))
const isLoading = ref(false)
const showSuccessMessage = ref(false)

// Modal Control
const showInOutModal = ref(false)
const selectedProduct = ref({})
const inOutNote = ref('')

// Customer and Address
const customers = ref([])
const addresses = ref([])
const customerSearch = ref('')
const showNewCustomerForm = ref(false)
const newCustomer = ref({ name: '', phone: '' })
const showNewAddressForm = ref(false)
const newAddress = ref({ address_line: '', city: '', postal_code: '' })

// Fetch Inventory List for Selected Date
const fetchInventory = async () => {
  try {
    const response = await axios.get('/api/inventory', {
      params: {
        date: selectedDate.value
      }
    })
    products.value = response.data.products.map(product => {
      return {
        ...product,
        selected_inventory: product.selected_inventory ?? 0,
        yesterday_inventory: product.yesterday_inventory ?? 0
      }
    })
  } catch (error) {
    console.error('Error fetching inventory:', error)
  }
}

// Save Inventory
const saveInventory = async (product) => {
  isLoading.value = true
  try {
    await axios.post('/api/inventory/update', {
      products: [{
        id: product.id,
        inventory: product.selected_inventory,
        date: selectedDate.value
      }]
    })
    showSuccessMessage.value = true
    setTimeout(() => {
      showSuccessMessage.value = false
    }, 2000)
  } catch (error) {
    console.error('Error updating inventory:', error)
  } finally {
    isLoading.value = false
  }
}

// Reset to Yesterday's Value
const resetToYesterday = (product) => {
  product.selected_inventory = product.yesterday_inventory
  saveInventory(product)
}

// Open In/Out Modal
const openInOutModal = (product) => {
  selectedProduct.value = { ...product }
  inOutNote.value = ''
  showInOutModal.value = true
  fetchCustomers() // Load customers when the modal is opened
}

// Close In/Out Modal
const closeInOutModal = () => {
  showInOutModal.value = false
}

// Save In/Out Data
const saveInOut = async (product) => {
  isLoading.value = true
  try {
    await axios.post('/api/inventory/in_out', {
      product: {
        id: product.id
      },
      io_amount: product.in_out,
      date: selectedDate.value,
      note: inOutNote.value,
      from_customer_id: product.from_customer_id,
      to_customer_id: product.to_customer_id
    })
    fetchInventory() // Refresh the data after saving
    showSuccessMessage.value = true
    setTimeout(() => {
      showSuccessMessage.value = false
    }, 2000)
    closeInOutModal()
  } catch (error) {
    console.error('Error saving in/out data:', error)
    alert('Failed to save in/out data. Please try again.')
  } finally {
    isLoading.value = false
  }
}

// Lazy Load Customers on Demand with Debounced Search
const fetchCustomers = debounce(async () => {
  try {
    const response = await axios.get('/api/customers', {
      params: {
        search: customerSearch.value
      }
    })
    customers.value = response.data.customers
  } catch (error) {
    console.error('Error fetching customers:', error)
  }
}, 300)  // Debounced by 300ms

// Watch for Search Changes
watch(customerSearch, () => {
  fetchCustomers()
})

// Create New Customer
const createNewCustomer = async () => {
  try {
    const response = await axios.post('/api/customers', newCustomer.value)
    newCustomer.value = { name: '', phone: '' }
    showNewCustomerForm.value = false
    fetchCustomers() // Refresh the list after adding
  } catch (error) {
    console.error('Error creating new customer:', error)
    alert('Failed to create new customer. Please try again.')
  }
}

// Create New Address
const createNewAddress = async () => {
  try {
    const response = await axios.post('/api/customer-addresses', newAddress.value)
    newAddress.value = { address_line: '', city: '', postal_code: '' }
    showNewAddressForm.value = false
    fetchAddresses() // Refresh the list after adding
  } catch (error) {
    console.error('Error creating new address:', error)
    alert('Failed to create new address. Please try again.')
  }
}

// Save New Order to Backend
const saveOrder = async () => {
  try {
    const orderedIds = products.value.map(product => product.id)
    await axios.post('/api/inventory/order', {
      ordered_ids: orderedIds
    })
  } catch (error) {
    console.error('Error saving order:', error)
  }
}

onMounted(() => {
  fetchInventory()
})
</script>