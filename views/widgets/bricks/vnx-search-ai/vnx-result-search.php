<div id="vnx-result-search-ai" v-cloak class="w-full mt-6 px-2">
    <!-- Loading khi tìm kiếm bằng AI -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-12 text-gray-500">
        <svg class="w-16 h-16 mb-4 text-blue-200 animate-spin" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="text-lg font-semibold mb-1">Đang tìm kiếm...</div>
        <div class="text-sm">Vui lòng chờ trong giây lát.</div>
    </div>
    <!-- Loading khi tải tất cả bài viết mặc định -->
    <div v-if="isLoadingAll && !isLoading" class="flex flex-col items-center justify-center py-12 text-gray-500">
        <svg class="w-12 h-12 mb-4 text-blue-300 animate-spin" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="text-base font-medium mb-1">Đang tải danh sách bài viết...</div>
    </div>
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ error }}</div>

    <div class="flex flex-col lg:flex-row gap-8 w-full max-w-6xl mx-auto mt-8 min-h-[60vh]" v-if="!isLoading && !isLoadingAll">
        <!-- Main content: Kết quả tìm kiếm -->
        <div class="flex-1 order-2 lg:order-1">
            <div class="mb-2 text-gray-700 text-sm font-medium">
                {{ searchValue ? 'Đã tìm thấy' : 'Tổng số' }} {{ filteredResults.length }} bài viết
            </div>
            <div v-if="!filteredResults.length && !isLoading && !isLoadingAll && !error" class="flex flex-col items-center justify-center py-12 text-gray-500">
                <svg class="w-16 h-16 mb-4 text-blue-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-lg font-semibold mb-1">Không tìm thấy bài viết phù hợp</div>
                <div class="text-sm">Vui lòng thử lại với từ khóa hoặc bộ lọc khác.</div>
            </div>
            <ul class="vnx-search-results space-y-4 text-left">
                <li v-for="item in paginatedResults" :key="item.link" class="p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow border border-gray-100 relative">
                    <a :href="item.link" target="_blank" class="block text-blue-700 hover:text-blue-900 text-lg vnx_tablet:text-base font-semibold mb-1 pr-0 lg:pr-24 break-words">{{ decodeHtmlEntities(item.title) }}</a>
                    <div class="vnx-search-excerpt text-gray-600 text-sm vnx_tablet:text-xs">{{ item.excerpt }}</div>
                    <div class="absolute vnx_tablet:static top-4 right-4 vnx_tablet:top-0 vnx_tablet:right-0 z-10 mt-2 lg:mt-0">
                        <span v-if="item.categories" class="bg-sky-100 text-sky-800 text-xs vnx_tablet:text-[11px] font-medium px-2.5 py-0.5 rounded-sm">
                            {{ item.categories }}
                        </span>
                    </div>
                </li>
            </ul>
            <div class="flex flex-wrap justify-center mt-6 gap-1" v-if="totalPages > 1">
                <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 rounded border bg-gray-100 text-gray-700 hover:bg-blue-100 disabled:opacity-50 flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Prev
                </button>
                <template v-for="page in totalPages">
                    <button v-if="Math.abs(page-currentPage)<=2 || page===1 || page===totalPages" :key="page" @click="changePage(page)" :class="['px-3 py-1 rounded border', currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-blue-100']">{{ page }}</button>
                    <span v-else-if="page === currentPage-3 || page === currentPage+3" :key="'ellipsis-'+page" class="px-2">...</span>
                </template>
                <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1 rounded border bg-gray-100 text-gray-700 hover:bg-blue-100 disabled:opacity-50 flex items-center justify-center">
                    Next
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
        <!-- Sidebar: Filter + Download -->
        <aside class="flex flex-col gap-4 w-full sm:min-w-[250px] sm:max-w-[260px] sticky vnx_tablet:min-w-full vnx_tablet:static top-8 h-fit items-end bg-white/80 rounded-lg shadow p-4 order-1 lg:order-2 mb-4 lg:mb-0">
            <div class="flex gap-2 flex-wrap w-full items-start">
                <div
                    v-for="cat in uniqueCategories"
                    :key="cat.name"
                    @click="toggleCategory(cat.name)"
                    :class="[
                            'relative text-xs hover:cursor-pointer font-bold inline-block rounded-md px-5 py-2 m-1 text-center shadow hover:shadow-lg transition',
                            selectedCategories.includes(cat.name)
                            ? 'text-white bg-gradient-to-r from-blue-300 to-blue-400'
                            : 'text-gray-700 !bg-gray-200']">
                    <span class="absolute -top-[0.75rem] -right-[0.5rem] min-w-[24px] bg-white text-orange-500 text-small rounded-full px-1 py-0.5 border-2 border-orange-200 shadow-sm">
                        {{ cat.count }}
                    </span>
                    {{ cat.name }}
                </div>
            </div>
            <div class="w-full flex flex-col gap-2 mt-4">
                <select v-model="downloadType" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-md text-gray-600 ">
                    <option value="txt">Định dạng .txt</option>
                    <option value="csv">Định dạng .csv</option>
                    <option value="json">Dịnh dạng .json</option>
                </select>
                <button
                    @click="handleDownload"
                    type="button"
                    class="flex items-center px-4 py-2 text-white rounded shadow text-sm font-semibold w-full justify-center"
                    style="background: linear-gradient(97.32deg, #FFBD2A 5%, #FD7659 170.21%)">
                    <i class="fas fa-download w-4 h-4 mr-2"></i>
                    Tải xuống
                </button>
                <button
                    @click="downloadAllPosts"
                    :disabled="isDownloadingAll"
                    type="button"
                    class="flex items-center px-4 py-2 text-white rounded shadow text-sm font-semibold w-full justify-center disabled:opacity-60 disabled:cursor-not-allowed"
                    style="background: linear-gradient(97.32deg, #3B82F6 5%, #6366F1 170.21%)">
                    <svg v-if="isDownloadingAll" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <i v-else class="fas fa-database w-4 h-4 mr-2"></i>
                    {{ isDownloadingAll ? 'Đang tải...' : 'Tải xuống tất cả bài viết' }}
                </button>
            </div>
        </aside>
    </div>

</div>