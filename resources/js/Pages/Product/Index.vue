<template>
    <Head title="Product" />

    <AuthenticatedLayout>
    <div class="p-4 bg-gray-100 min-h-screen">
      <!-- Header and Create Button -->
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Product List</h1>
        <button 
          @click="openCreateModal" 
          class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition"
        >
          Add Product
        </button>
      </div>
  
      <!-- Filters Section -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-4 md:space-y-0 md:space-x-4">
        <input 
          v-model="filters.search" 
          type="text" 
          placeholder="Search products..." 
          class="w-full md:w-1/3 border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:border-blue-400 transition"
          @input="fetchProducts"
        />
        <div class="mt-6 flex justify-center space-x-2">
  <button 
    :disabled="!products.prev_page_url" 
    @click="changePage(products.current_page - 1)" 
    class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition disabled:bg-gray-300 disabled:cursor-not-allowed"
  >
    Previous
  </button>
  <button 
    :disabled="!products.next_page_url" 
    @click="changePage(products.current_page + 1)" 
    class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition disabled:bg-gray-300 disabled:cursor-not-allowed"
  >
    Next
  </button>
</div>
      </div>
  
      <!-- Product Cards for Mobile -->
<!-- Product Cards with Image on Left, Consistent Layout Across All Screen Sizes -->
    <div class="block lg:hidden grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3">
        <div 
            v-for="product in products.data" 
            :key="product.id" 
            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-row items-center space-x-4"
        >
        <!-- Image Section -->
        <div class="w-24 h-24 flex-shrink-0">
        <img 
            :src="product.image" 
            alt="Product Image" 
            class="w-full h-full object-contain rounded-l-lg"
        />
        </div>

        <!-- Details Section -->
        <div class="flex-1 p-4 flex flex-col justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-800 leading-tight">{{ product.name }}</h2>
            <p class="text-sm text-gray-500">{{ product.category }}</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ formatPrice(product.price) }}</p>
            <p class="text-gray-600 text-xs mt-1">SKU: {{ product.sku }}</p>
            <p class="text-gray-600 text-xs">Alias: {{ product.alias }}</p>
        </div>
        <div class="flex justify-end space-x-2 mt-2">
            <!-- Edit Icon -->
            <button 
            @click="openEditModal(product)" 
            class="text-yellow-500 hover:text-yellow-600 transition"
            title="Edit"
            >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M17.414 2.586a2 2 0 010 2.828l-1.828 1.828-2.828-2.828 1.828-1.828a2 2 0 012.828 0zM4 13v3h3l8.293-8.293-2.828-2.828L4 13z" />
            </svg>
            </button>
            <!-- Delete Icon -->
            <button 
            @click="confirmDelete(product.id)" 
            class="text-red-500 hover:text-red-600 transition"
            title="Delete"
            >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v1H3a1 1 0 100 2h14a1 1 0 100-2h-1V4a2 2 0 00-2-2H6zM4 7h12l-1 10a2 2 0 01-2 2H7a2 2 0 01-2-2L4 7z" clip-rule="evenodd" />
            </svg>
            </button>
        </div>
        </div>
    </div>
    </div>
  
      <!-- Product Table for Tablet and Desktop -->
      <div class="hidden lg:block overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="min-w-full border-collapse">
          <thead>
            <tr class="bg-blue-500 text-white text-left">
              <th class="py-3 px-4 font-semibold">Image</th>
              <th class="py-3 px-4 font-semibold">Name</th>
              <th class="py-3 px-4 font-semibold">Category</th>
              <th class="py-3 px-4 font-semibold">Price</th>
              <th class="py-3 px-4 font-semibold">SKU</th>
              <th class="py-3 px-4 font-semibold">Alias</th>
              <th class="py-3 px-4 font-semibold text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="product in products.data" 
              :key="product.id" 
              class="border-b hover:bg-gray-100 transition"
            >
              <td class="py-3 px-4">
                <img 
                  :src="product.image" 
                  alt="Product Image" 
                  class="w-16 h-16 object-cover rounded-lg shadow-md"
                />
              </td>
              <td class="py-3 px-4">{{ product.name }}</td>
              <td class="py-3 px-4">{{ product.category }}</td>
              <td class="py-3 px-4">{{ formatPrice(product.price) }}</td>
              <td class="py-3 px-4">{{ product.sku }}</td>
              <td class="py-3 px-4">{{ product.alias }}</td>
              <td class="py-3 px-4 text-center">
                <button 
                  @click="openEditModal(product)" 
                  class="text-yellow-500 hover:text-yellow-600 transition"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M17.414 2.586a2 2 0 010 2.828l-1.828 1.828-2.828-2.828 1.828-1.828a2 2 0 012.828 0zM4 13v3h3l8.293-8.293-2.828-2.828L4 13z" />
                  </svg>
                </button>
                <button 
                  @click="confirmDelete(product.id)" 
                  class="text-red-500 hover:text-red-600 transition"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v1H3a1 1 0 100 2h14a1 1 0 100-2h-1V4a2 2 0 00-2-2H6zM4 7h12l-1 10a2 2 0 01-2 2H7a2 2 0 01-2-2L4 7z" clip-rule="evenodd" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal for Create and Update -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md relative">
        <h2 class="text-xl font-bold mb-4">{{ isEditMode ? 'Edit Product' : 'Add Product' }}</h2>
        <input 
        v-model="form.name" 
        type="text" 
        placeholder="Product Name" 
        class="w-full mb-4 border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:border-blue-400 transition"
        />
        <input 
        v-model="form.category" 
        type="text" 
        placeholder="Category" 
        class="w-full mb-4 border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:border-blue-400 transition"
        />
        <input 
        v-model="form.price" 
        type="number" 
        placeholder="Price" 
        class="w-full mb-4 border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:border-blue-400 transition"
        />
        <div class="flex justify-end space-x-2">
        <button 
            @click="closeModal" 
            class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500 transition"
        >
            Cancel
        </button>
        <button 
            @click="isEditMode ? updateProduct() : createProduct()" 
            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition"
        >
            {{ isEditMode ? 'Update' : 'Create' }}
        </button>
        </div>
        <!-- Close Button (X) -->
        <button 
        @click="closeModal" 
        class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 transition"
        >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M6 4a1 1 0 00-1 1v10a1 1 0 001 1h8a1 1 0 001-1V5a1 1 0 00-1-1H6zm-2 1a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd" />
            <path fill-rule="evenodd" d="M9 7a1 1 0 112 0v6a1 1 0 11-2 0V7z" clip-rule="evenodd" />
            <path fill-rule="evenodd" d="M4 4a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1z" clip-rule="evenodd" />
        </svg>
        </button>
    </div>
    </div>
    </AuthenticatedLayout>

</template>
  
  <script setup>
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
  import { Head } from '@inertiajs/vue3';
  import { ref, onMounted } from 'vue'
  import axios from 'axios'
  
  const products = ref({
    data: [],
    current_page: 1,
    next_page_url: null,
    prev_page_url: null
  })
  
  const filters = ref({
    store_id: '',
    search: ''
  })
  
  const stores = ref([]) 
  const showModal = ref(false)
  const isEditMode = ref(false)
  const form = ref({ id: null, name: '', category: '', price: '' })
  
  // Fetch Products
  const fetchProducts = async () => {
    const response = await axios.get('/api/product', { params: { ...filters.value, page: products.value.current_page } })
    products.value = response.data.data
  }
  
  // Create
  const createProduct = async () => {
    await axios.post('/api/product', form.value)
    fetchProducts()
    closeModal()
  }
  
  // Edit
  const openEditModal = (product) => {
    isEditMode.value = true
    form.value = { ...product }
    showModal.value = true
  }
  
  const updateProduct = async () => {
    await axios.put(`/api/product/${form.value.id}`, form.value)
    fetchProducts()
    closeModal()
  }
  
  // Delete
  const confirmDelete = async (id) => {
    if (confirm('Are you sure you want to delete this product?')) {
      await axios.delete(`/api/product/${id}`)
      fetchProducts()
    }
  }
  
  // Modal Control
  const openCreateModal = () => {
    isEditMode.value = false
    form.value = { name: '', category: '', price: '' }
    showModal.value = true
  }
  
  const closeModal = () => {
    showModal.value = false
  }
  
  // Lifecycle Hooks
  onMounted(() => {
    fetchProducts()
  })
  
  // Change Page
  const changePage = (page) => {
    products.value.current_page = page
    fetchProducts()
  }

  const formatPrice = (price) => {
  return new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' }).format(price)
}
  </script>