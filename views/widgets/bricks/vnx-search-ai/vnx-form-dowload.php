<div id="vnx-search-ai"  class="w-full max-w-full mx-auto shadow ">
    <form class="w-full" @submit.prevent="handleSearch">
        <label for="default-search" class="mb-2 text-sm font-medium text-gray-900 sr-only">Tìm kiếm</label>
        <div class="relative w-full">
            <textarea id="default-search" class="block w-full p-4 ps-10 text-md text-gray-700 border border-gray-300 rounded-lg" placeholder="Nhập danh sách url..." v-model="searchValue" required rows="3"></textarea>
            <button
                type="submit"
                style="background: linear-gradient(97.32deg, #FFBD2A 5%, #FD7659 170.21%)"
                class="text-white absolute end-2.5 bottom-2.5 w-[150px] rounded-lg hover:opacity-90   font-medium text-sm px-4 py-2 transition">
                Tìm kiếm
            </button>
        </div>
    </form>
</div>