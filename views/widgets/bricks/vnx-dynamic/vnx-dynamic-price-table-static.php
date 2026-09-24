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

<div class="vnx-dynamic-price-table vnx-dynamic-price-table-static w-full" v-cloak>

    <div class="flex items-start gap-8 vnx_tablet:flex-col vnx_tablet:gap-4">

        <!-- Cấu hình tài nguyên -->
        <div class="table-static-right flex-1 min-w-0 w-full bg-white border border-[#e0e8f2] rounded-[16px] shadow-[0px_2px_6px_rgba(0,0,0,0.05)] p-6 vnx_tablet:p-4 flex flex-col gap-[27px] vnx_tablet:gap-4">

            <div class="title flex flex-col gap-5 w-full">
                <p class="font-bold text-[20px] leading-[29px] text-[#1f2937]">Cấu hình tài nguyên</p>
                <div class="h-px w-full bg-[#e0e8f2]"></div>
            </div>

            <div class="service flex flex-col gap-7 vnx_tablet:gap-4 w-full">
                <template v-for="(resource, index) in resources" :key="resource.key">
                    <div class="flex flex-col gap-[10px] w-full">
                        <div class="flex items-center vnx_tablet:h-auto w-full">
                            <p class="flex-1 font-semibold text-[16px] leading-6 vnx_tablet:text-[16px] vnx_tablet:leading-6 vnx_tablet:tracking-normal text-[#1f2937]">{{ resource.label }}</p>
                        </div>
                        <div class="flex flex-col gap-3 w-full">
                            <div class="range-slider">
                                <input type="range" min="0" :max="resource.max - resource.min" :step="resource.step" v-model="resource.value" class="slider" :id="`${resource.key}_range`">
                                <div class="slider-thumb">
                                    <div class="tooltip">{{ Number(resource.value) + Number(resource.min) }}</div>
                                </div>
                                <div class="progress"></div>
                            </div>
                            <div class="flex items-center justify-between w-full text-[15px] leading-[17px] vnx_tablet:text-[14px] vnx_tablet:leading-5 vnx_tablet:tracking-[-0.12px] text-[#4b5563]">
                                <span>{{ Number(resource.min) }}</span>
                                <span>{{ Number(resource.max) }}</span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Dịch vụ khác -->
            <div class="service-order bg-[#eff6ff] rounded-[10px] p-4 flex flex-col gap-3 w-full">
                <div class="flex items-center justify-between w-full hover:cursor-pointer" v-on:click="toggleExtraResources">
                    <p class="font-semibold text-[16px] leading-6 text-[#1f2937]">Dịch vụ khác</p>
                    <div class="flex items-center gap-2">
                        <span class="text-[14px] leading-5 tracking-[-0.12px] text-[#0d6bf0] whitespace-nowrap">{{ extraResources.length }} tùy chọn</span>
                        <i class="fas text-[#0071E6] text-[12px]" :class="showExtraResources ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </div>
                </div>
                
                <div v-show="showExtraResources">
                  <div class="h-px w-full bg-[#d6deeb]" style="margin: 12px 0;"></div>
                    <div class="flex flex-row vnx_tablet:flex-col flex-wrap vnx_tablet:flex-nowrap gap-4 w-full pt-1 vnx-resource-orders">
                        <template v-for="(resource, index) in extraResources" :key="resource.key">
                            <div class="flex flex-col gap-2 w-full" :class="`resources-${index + 1}`">
                                <label class="font-semibold text-[16px] leading-6 text-[#1f2937] w-full" :for="`${resource.key}_range`">{{ resource.label }}:</label>
                                <div class="flex flex-col gap-3 w-full">
                                    <div class="range-slider range-slider--muted">
                                        <input type="range" min="0" :max="resource.max - resource.min" :step="resource.step" v-model="resource.value" class="slider" :id="`${resource.key}_range`">
                                        <div class="slider-thumb">
                                            <div class="tooltip">{{ Number(resource.value) + Number(resource.min) }}</div>
                                        </div>
                                        <div class="progress"></div>
                                    </div>
                                    <div class="flex items-center justify-between w-full text-[15px] leading-[17px] vnx_tablet:text-[14px] vnx_tablet:leading-5 vnx_tablet:tracking-[-0.12px] text-[#4b5563]">
                                        <span>{{ Number(resource.min) }}</span>
                                        <span>{{ Number(resource.max) }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ước tính chi phí -->
        <div class="table-static-left w-[420px] vnx_tablet:w-full shrink-0 bg-white border border-[#e5e7eb] rounded-[16px] shadow-[0px_2px_6px_rgba(0,0,0,0.05)] p-6 vnx_tablet:p-4 flex flex-col">

            <div class="title flex flex-col gap-5 w-full">
                <p class="font-bold text-[20px] leading-[29px] text-[#111827]">Ước tính chi phí/ tháng</p>
                <div class="h-px w-full bg-[#e0e8f2]"></div>
            </div>

            <div class="service flex flex-col w-full">
                <template v-for="(resource, index) in resources" :key="resource.key">
                    <div class="border-b border-[#e0e8f2] py-3 vnx_tablet:py-4 flex flex-col gap-2 vnx_tablet:gap-1 w-full">
                        <div class="flex items-start justify-between gap-3 w-full text-[16px] leading-76 text-[#1f2937]">
                            <p class="font-semibold text-[16px] leading-6 vnx_tablet:tracking-normal">{{ trimTitle(resource.label) }}</p>
                            <p class="font-bold whitespace-nowrap text-[18px] tracking-[-0.14px] leading-7">{{ formatCurrency(resource.price ? resourceCost(resource) : '0') }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-3 w-full">
                            <div v-if="isRouterResource(resource)" class="text-[16px] leading-[19px] vnx_tablet:text-[14px] vnx_tablet:leading-5 vnx_tablet:tracking-[-0.12px] text-[#4b5563]">
                                <p>{{ Number(resource.min) }} {{ trimTitle(resource.label).toLowerCase() }} miễn phí</p>
                                <p v-if="Number(resource.value) > 0">{{ Number(resource.value) }} {{ trimTitle(resource.label).toLowerCase() }} × {{ formatCurrency(resource.price) }}</p>
                            </div>
                            <p v-else class="text-[16px] leading-[19px] vnx_tablet:text-[14px] vnx_tablet:leading-5 vnx_tablet:tracking-[-0.12px] text-[#4b5563]">{{ resource.desUnit }}</p>

                            <div class="flex items-center h-[34px] border border-[#e0e8f2] rounded-[8px] overflow-hidden shrink-0">
                                <button @click="decrement(index)" :disabled="Number(resource.value) === 0" class="w-[42px] h-full flex items-center justify-center disabled:opacity-30">
                                    <span class="block w-3 h-[1.5px] rounded-[1px] bg-[#1f2937]"></span>
                                </button>
                                <div class="h-full flex items-center justify-center border-x border-[#e0e8f2]">
                                    <input
                                        type="number"
                                        min="0"
                                        class="font-medium text-[16px] leading-6 text-[#1f2937] text-center border-none px-2 w-[59px]"
                                        :value="Number(resource.value) + Number(resource.min)"
                                        @change="onchangeInput($event, index)"
                                        @keypress.enter="onchangeInput($event, index)"
                                        onkeypress="return (event.charCode >= 48 && event.charCode <= 57)" />
                                </div>
                                <button @click="increment(index)" :disabled="Number(resource.value) >= Number(resource.max) - Number(resource.min)" class="w-[42px] h-full flex items-center justify-center disabled:opacity-30">
                                    <span class="relative block size-3">
                                        <span class="absolute left-0 top-[5.25px] block w-3 h-[1.5px] rounded-[1px] bg-[#1f2937]"></span>
                                        <span class="absolute top-0 left-[5.25px] block h-3 w-[1.5px] rounded-[1px] bg-[#1f2937]"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <div v-if="resource.warning" class="flex items-center justify-end gap-1 w-full text-[14px] leading-5 text-[#d97706]">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" class="shrink-0">
                                <path d="M10 2L18 17H2L10 2Z" stroke="#d97706" stroke-width="1.5" stroke-linejoin="round" />
                                <path d="M10 8V11.5" stroke="#d97706" stroke-width="1.5" stroke-linecap="round" />
                                <circle cx="10" cy="14" r="0.9" fill="#d97706" />
                            </svg>
                            <span>{{ resource.warning }}</span>
                        </div>
                    </div>
                </template>

                <template v-for="(resource, index) in extraResources" :key="resource.key">
                    <div v-if="resource.value > 0" class="border-b border-[#e0e8f2] py-3 flex flex-col gap-2 w-full">
                        <div class="flex items-start justify-between gap-3 w-full text-[#1f2937]">
                            <p class="font-semibold vnx_tablet:text-[16px] vnx_tablet:leading-6 vnx_tablet:tracking-normal">{{ trimTitle(resource.label) }}</p>
                            <p class="font-bold whitespace-nowrap text-[18px] leading-7 tracking-[-0.14px]">{{ formatCurrency(resource.price ? resourceCost(resource) : '0') }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-3 w-full">
                            <div v-if="isRouterResource(resource)" class="text-[16px] leading-[19px] vnx_tablet:text-[14px] vnx_tablet:leading-5 vnx_tablet:tracking-[-0.12px] text-[#4b5563]">
                                <p>{{ Number(resource.min) }} {{ trimTitle(resource.label).toLowerCase() }} miễn phí</p>
                                <p v-if="Number(resource.value) > 0">{{ Number(resource.value) }} {{ trimTitle(resource.label).toLowerCase() }} × {{ formatCurrency(resource.price) }}</p>
                            </div>
                            <p v-else class="text-[16px] leading-[19px] vnx_tablet:text-[14px] vnx_tablet:leading-5 vnx_tablet:tracking-[-0.12px] text-[#4b5563]">{{ resource.desUnit }}</p>

                            <div class="flex items-center h-[34px] border border-[#e0e8f2] rounded-[8px] overflow-hidden shrink-0">
                                <button @click="decrement(index, true)" :disabled="Number(resource.value) === 0" class="w-[42px] h-full flex items-center justify-center disabled:opacity-30">
                                    <span class="block w-3 h-[1.5px] rounded-[1px] bg-[#1f2937]"></span>
                                </button>
                                <div class="h-full flex items-center justify-center border-x border-[#e0e8f2]">
                                    <input
                                        type="number"
                                        min="0"
                                        class="font-medium text-[16px] leading-6 text-[#1f2937] text-center border-none px-2 w-[59px]"
                                        :value="Number(resource.value) + Number(resource.min)"
                                        @change="onchangeInput($event, index, true)"
                                        @keypress.enter="onchangeInput($event, index, true)"
                                        onkeypress="return (event.charCode >= 48 && event.charCode <= 57)" />
                                </div>
                                <button @click="increment(index, true)" :disabled="Number(resource.value) >= Number(resource.max) - Number(resource.min)" class="w-[42px] h-full flex items-center justify-center disabled:opacity-30">
                                    <span class="relative block size-3">
                                        <span class="absolute left-0 top-[5.25px] block w-3 h-[1.5px] rounded-[1px] bg-[#1f2937]"></span>
                                        <span class="absolute top-0 left-[5.25px] block h-3 w-[1.5px] rounded-[1px] bg-[#1f2937]"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <div v-if="resource.warning" class="flex items-center justify-end gap-1 w-full text-[14px] leading-5 text-[#d97706]">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" class="shrink-0">
                                <path d="M10 2L18 17H2L10 2Z" stroke="#d97706" stroke-width="1.5" stroke-linejoin="round" />
                                <path d="M10 8V11.5" stroke="#d97706" stroke-width="1.5" stroke-linecap="round" />
                                <circle cx="10" cy="14" r="0.9" fill="#d97706" />
                            </svg>
                            <span>{{ resource.warning }}</span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="service-order flex flex-col gap-4 pt-4 vnx_tablet:pt-4 w-full">
                <div class="flex flex-col gap-1 vnx_tablet:gap-1 w-full">
                    <div class="flex items-center justify-between gap-3 w-full">
                        <p class="font-bold text-[20px] leading-[30px] tracking-[-0.02px] text-[#1f2937]">Tổng cộng</p>
                        <p class="font-bold text-[20px] leading-[30px] tracking-[-0.02px] vnx_tablet:text-[24px] vnx_tablet:leading-[35px] text-[#111827] text-right">{{ formatCurrency(totalBill) }}</p>
                    </div>
                    <p class="text-[16px] leading-[19px] vnx_tablet:text-[14px] vnx_tablet:leading-5 vnx_tablet:tracking-[-0.12px] text-[#4b5563]">Giá chưa bao gồm VAT</p>
                </div>

                <button :disabled="isLoading" @click="orderProduct" class="disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 bg-[#007cfc] hover:bg-[#085fc5] transition-colors duration-200 rounded-[10px] text-white w-full py-3 px-5 text-[18px] leading-7 tracking-[-0.14px] font-medium">
                    <span v-if="isLoading" class="flex">
                        <svg class="animate-[spin_0.5s_linear_infinite] -ml-1 mr-1 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    Đăng ký ngay
                </button>
            </div>
        </div>
    </div>
</div>
