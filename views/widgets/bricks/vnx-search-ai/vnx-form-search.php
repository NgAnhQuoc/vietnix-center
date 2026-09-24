
<div id="vnx-search-ai"  class="w-full max-w-full mx-auto shadow ">
    <form class="w-full" @submit.prevent="handleSearch">
        <label for="default-search" class="mb-2 text-sm font-medium text-gray-900 sr-only">Tìm kiếm</label>
        <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                </svg>
            </div>
            <input type="search" id="default-search" class="block  w-full p-4 ps-10 text-md text-gray-700 border border-gray-300 rounded-lg  " placeholder="Nhập từ khoá tìm kiếm..." v-model="searchValue" @input="handleInput" required />
            <button
                type="submit"
                style="background: linear-gradient(97.32deg, #FFBD2A 5%, #FD7659 170.21%)"
                class="text-white absolute end-2.5 bottom-2.5 w-[150px] rounded-lg hover:opacity-90   font-medium text-sm px-4 py-2 transition">
                Tìm kiếm
            </button>
        </div>
    </form>
</div>