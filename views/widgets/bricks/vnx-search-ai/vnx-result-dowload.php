<div id="vnx-result-dowload" class="w-full mt-6 px-2" v-if="data || isLoadingPrice || aiPriceError">
    <div v-if="isLoadingPrice" class="w-full flex justify-center items-center py-8">
        <span class="text-lg text-gray-500 flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i> Đang tải dữ liệu...</span>
    </div>
    <div v-else-if="aiPriceError" class="w-full flex justify-center items-center py-8">
        <span class="text-red-600 text-base">{{ aiPriceError }}</span>
        <button @click="handleClose" class="ml-2 px-3 py-2 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300" title="Đóng"><i class="fas fa-times"></i></button>
    </div>
    <div v-else class="w-full flex flex-col gap-2 mt-4">
        <select v-model="downloadType" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-md text-gray-600 ">
            <option value="txt">Định dạng .txt</option>
            <option value="json">Dịnh dạng .json</option>
        </select>
        <div class="flex justify-between items-center mt-2">
            <button
                @click="handleDownload"
                type="button"
                class="flex items-center px-4 py-2 text-white rounded shadow text-sm font-semibold w-full justify-center"
                style="background: linear-gradient(97.32deg, #FFBD2A 5%, #FD7659 170.21%)">
                <i class="fas fa-download w-4 h-4 mr-2"></i>
                Tải xuống
            </button>
            <button
                @click="handleClose"
                type="button"
                class="ml-2 px-3 py-2 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300"
                title="Đóng">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <pre>{{ formatJson(data) }}</pre>
    </div>
</div>
