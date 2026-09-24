<?php
$settings = $data->settings ?? [];

$vnxData = [
    'mainResources'  => $settings['main-resources'] ?? [],
    'extraResources' => $settings['extra-resources'] ?? [],
    'pid'            => $settings['pid'] ?? '',
    'billingcycle'   => $settings['billingcycle'] ?? 'quarterly',
];
?>

<script>
    window.vnxDynamicPriceTableData = <?php echo wp_json_encode($vnxData); ?>;
</script>


<div class="vnx-dynamic-price-table dynamic w-full" v-cloak>
    <div class="flex vnx_tablet:flex-col items-start gap-6 vnx_tablet:gap-4">

        <!-- Bảng thông báo  -->
        <div v-if="noticeMessage" class="fixed inset-0 z-10 flex items-center justify-center">
            <div class="absolute inset-0 bg-black opacity-20"></div>

            <div class="relative flex flex-col bg-white px-5 py-2 rounded-xl z-20 text-[#282829] text-[18px] vnx_tablet:text-[14px] min-w-[278px] vnx_tablet:min-w-[225px]">
                <span class="center border border-x-0 border-t-0 border-b-[#C0C0C2] h-[56px]">
                    <span v-html="noticeMessage"></span>
                </span>

                <button @click="resetMessage" class="text-[#007CFC] py-3 ">
                    Đồng ý
                </button>
            </div>


        </div>
        <!-- Điều chính bảng giá  -->
        <div class="flex-1 vnx_tablet:w-full bg-[#F6F7F9] rounded-lg p-5 vnx_tablet:p-3 vnx_tablet:pt-0 pt-0 text-lg vnx_tablet:text-[14px] vnx_tablet:leading-[24px] leading-[30px]">
            <div class="mb-3">
                <p class="font-bold  py-3 border-b-[1px] border-b-[#C0C0C2]">
                    Chọn tài nguyên
                </p>
                <div class="vnx-wrap-resources flex flex-col gap-4 vnx_tablet:gap-2 mt-4 vnx_tablet:mt-3">
                    <template v-for="resource in resources" :key="resource.key">
                        <div class="text-[#000]">

                            <span>{{ resource.label }}:</span>
                            <div class="range-slider">
                                <input type="range" min="0" :max="resource.max - resource.min" :step="resource.step" v-model="resource.value" class="slider" :id="`${resource.key}_range`">
                                <div class="slider-thumb">
                                    <div class="tooltip">{{ Number(resource.value) + Number(resource.min) }}</div>
                                </div>
                                <div class="progress"></div>
                            </div>
                            <div class="w-full flex justify-between text-base vnx_tablet:text-[12px] left-[22px] leading-6">
                                <span class="ml-2">{{Number(resource.min) }}</span>
                                <span>{{ Number(resource.max) }}</span>
                            </div>
                        </div>
                    </template>
                </div>

            </div>
            <div class="text-lg vnx_tablet:text-[14px] vnx_tablet:leading-[24px] leading-[30px]">
                <div class="font-bold flex justify-between py-3 border-b-[1px] border-b-[#C0C0C2] hover:cursor-pointer" v-on:click="toggleExtraResources">
                    <p> Dịch vụ khác</p>
                    <span><i class="fas text-[#282829]" :class="showExtraResources ? 'fa-chevron-up' : 'fa-chevron-down'"></i></span>
                </div>

                <div v-show="showExtraResources">
                    <div class="vnx-wrap-extraResources grid grid-cols-2 vnx_tablet:grid-cols-1 gap-x-6 gap-y-4 vnx_tablet:gap-y-2 mt-4 text-[#000]">
                        <template v-for="resource in extraResources" :key="resource.key">
                            <div>
                                <label class="text-[#000]" :for="`${resource.key}_range`">{{ resource.label }}:</label>
                                <div class="range-slider">
                                    <input type="range" min="0" :max="resource.max - resource.min" :step="resource.step" v-model="resource.value" class="slider" :id="`${resource.key}_range`">
                                    <div class="slider-thumb">
                                        <div class="tooltip">{{ Number(resource.value) + Number(resource.min) }}</div>
                                    </div>
                                    <div class="progress"></div>
                                </div>
                                <div class="w-full flex justify-between">
                                    <span class="ml-2">{{ resource.min }}</span>
                                    <span>{{ resource.max }}</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin giá tạm tính -->
        <div class="vnx-wrap-temp-price w-[363px] flex justify-between flex-col vnx_tablet:w-full bg-[#F6F7F9] rounded-lg p-5 vnx_tablet:p-3 vnx_tablet:pt-0  pt-0 color-[#282829] text-base vnx_tablet:text-[14px]  leading-6">
            <div>
                <p class="title font-bold text-lg vnx_tablet:text-[14px] vnx_tablet:leading-6 leading-[30px] py-3 border-b-[1px] border-b-[#C0C0C2]">
                    Giá tạm tính cho 1 tháng
                </p>

                <!-- thông tin giá  -->
                <div class="list-source min-h-[260px] vnx_tablet:min-h-[0px]">
                    <template v-for="(resource, index) in resources" :key="resource.key">
                        <div class="item-source flex flex-col gap-1 border-b-[1px] border-b-[#C0C0C2] border-dashed pb-3 mt-3">
                            <div class="flex items-center justify-between w-full">
                                <p class="font-bold">{{ trimTitle(resource.label) }}</p>
                                <span class="price-amount font-bold">{{ formatCurrency(resource.price ? resource.price * (Number(resource.value) + Number(resource.min)) : '0') }}</span>

                            </div>
                            <div class="flex items-center justify-between w-full">
                                <p class="price-details">{{ resource.desUnit }}</p>

                                <!-- nút thêm trừ  -->
                                <div class="border border-[#C0C0C2] rounded flex">

                                    <button @click="decrement(index)" :disabled="Number(resource.value) === 0" class="disabled:opacity-20 size-8 vnx_tablet:size-7 border border-y-0 border-l-0 border-[#C0C0C2]">-</button>
                                    <input
                                        type="number"
                                        min="0"
                                        class="price-amount font-bold w-[60px] text-center border-none px-0"
                                        :value="Number(resource.value) + Number(resource.min)"
                                        @change="onchangeInput($event, index)"
                                        @keypress.enter="onchangeInput($event, index)"
                                        onkeypress="return (event.charCode >= 48 && event.charCode <= 57)" />
                                    <button @click="increment(index)" :disabled="Number(resource.value) >= Number(resource.max) - Number(resource.min)" class="disabled:opacity-20 size-8 vnx_tablet:size-7 border border-y-0 border-r-0 border-[#C0C0C2]">+</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-for="(resource, index) in extraResources" :key="resource.key">
                        <div v-if="resource.value > 0" class="item-source flex flex-col gap-1 border-b-[1px] border-b-[#C0C0C2] border-dashed pb-3 mt-3">
                            <div class="flex items-center justify-between w-full">
                                <p class="font-bold">{{ trimTitle(resource.label) }}</p>
                                <span class="price-amount font-bold">{{ formatCurrency(resource.price ? resource.price * (Number(resource.value) + Number(resource.min)) : '0') }}</span>

                            </div>
                            <div class="flex items-center justify-between w-full">
                                <p class="price-details">{{ resource.desUnit }}</p>

                                <!-- nút thêm trừ  -->
                                <div class="border border-[#C0C0C2] rounded flex">

                                    <button @click="decrement(index, true)" :disabled="Number(resource.value) === 0" class="disabled:opacity-20 size-8 vnx_tablet:size-7 border border-y-0 border-l-0 border-[#C0C0C2]">-</button>
                                    <input
                                        type="number"
                                        min="0"
                                        class="price-amount font-bold w-[60px] text-center border-none px-0"
                                        :value="Number(resource.value) + Number(resource.min)"
                                        @change="onchangeInput($event, index, true)"
                                        @keypress.enter="onchangeInput($event, index, true)"
                                        onkeypress="return (event.charCode >= 48 && event.charCode <= 57)" />
                                    <button @click="increment(index, true)"
                                        :disabled="Number(resource.value) >= Number(resource.max) - Number(resource.min)"
                                        class="disabled:opacity-20 size-8 vnx_tablet:size-7 border border-y-0 border-r-0 border-[#C0C0C2]">+</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>


            <!-- Tổng cộng -->
            <div class="vnx-temp-price border-t-[1px] border-t-[#C0C0C2] pt-3">
                <div class="flex justify-between items-start ">
                    <div class="flex flex-col">
                        <p class="font-bold">Tổng cộng</p>
                        <p class="text-sm leading-6 vnx_tablet:text-[12px] vnx_tablet:leading-5">Giá chưa bao gồm VAT</p>
                    </div>
                    <div class="font-bold">
                        <span>{{formatCurrency(totalBill)}}</span>
                    </div>
                </div>

                <button :disabled="isLoading" @click="orderProduct" class=" disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center bg-[#007CFC] hover:bg-[#085FC5] rounded-lg text-white w-full h-[54px] vnx_tablet:h-[40px]  vnx_tablet:text-[14px] mt-3 text-lg leading-8">
                    <span v-if="isLoading" class="flex"><svg class="animate-[spin_0.5s_linear_infinite]  -ml-1 mr-3 h-5 w-5 text-white "
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>

                    Đăng ký ngay
                </button>
            </div>
        </div>
    </div>
</div>