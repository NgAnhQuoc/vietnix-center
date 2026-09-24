<?php

/**
 * View: VNX Dynamic Price Object Storage V2
 */
$settings = $data->settings;
$storage_field = isset($settings['storage_field']) ? $settings['storage_field'] : [];
?>
<div class="vnx-obj-v2 w-full" id="vnx-obj-v2-<?php echo esc_attr($data->id); ?>" v-cloak
    data-settings='<?php echo json_encode($settings); ?>'>
    <!-- MAIN CARD -->
    <div
        class="bg-white border border-[#dedfe0] sm:rounded-xl rounded-[8px] px-4 py-5 md:px-5 md:py-5 flex flex-col lg:flex-row gap-6">

        <!-- LEFT PANEL -->
        <div class="flex flex-col gap-5 flex-1 min-w-0">

            <!-- Panel heading + Chu kỳ dropdown -->
            <div
                class="border-b border-[#dedfe0] pb-4 sm:pt-4 pt-0 sm:mr-4 flex flex-wrap items-center justify-between sm:gap-3 gap-[10px]">
                <p class="font-bold text-[14px] sm:text-[18px] leading-[24px] sm:leading-[30px]  text-[#282829]">
                    {{ leftHeading }}
                </p>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-[16px] leading-6 font-[400] text-[#282829] whitespace-nowrap">Chu kỳ :</span>
                    <div class="vnx-cycle-select-wrap relative">
                        <select v-model.number="cycleIndex" class="vnx-cycle-select">
                            <option v-for="(c, i) in cycles" :key="i" :value="i">{{ c.label }}</option>
                        </select>
                        <svg class="vnx-cycle-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9l6 6 6-6" stroke="#282829" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Resource blocks -->
            <div class="flex flex-col gap-5">

                <?php
                if (is_array($storage_field)) {
                    foreach ($storage_field as $key => $fileds) {
                        if ($fileds['type'] == 'storage') {
                ?>
                <!-- STORAGE -->
                <div class="flex flex-col gap-1 sm:p-4 p-3 rounded-[6px]" style="background:#fff">
                    <div class="flex items-center justify-between gap-4 w-full">
                        <div
                            class="flex sm:flex-row flex-col justify-between sm:items-center items-start sm:gap-4 gap-2 min-w-0 sm:flex-nowrap flex-wrap">
                            <div
                                class="flex items-center gap-2 font-semibold text-[16px] leading-6 shrink-0 whitespace-nowrap">
                                <span class="text-[#282829]"><?php echo $fileds['title']; ?></span>
                                <span
                                    class="text-[#747475] text-[12px] leading-[22px]">(<?php echo $fileds['unit']; ?>)</span>
                            </div>
                            <!-- Tier tag -->
                            <div v-if="storageTier.label"
                                class="flex items-center justify-center px-2 rounded-lg shrink-0 whitespace-nowrap"
                                :class="storageTier.tagBg">
                                <span class="font-medium text-[12px] leading-[22px]"
                                    :class="storageTier.tagColor">{{ storageTier.label }}</span>
                            </div>
                        </div>
                        <p
                            class="font-bold sm:text-[16px] text-[12px] sm:leading-6 leading-[22px] text-[#282829] shrink-0 whitespace-nowrap">
                            <template v-if="storageTier.price">{{ fmt(storageCost) }} VND</template>
                            <template v-else><span class="text-[#007cfc]">Liên hệ</span></template>
                        </p>
                    </div>

                    <div class="vnx-slider-wrap">
                        <input type="range" class="vnx-slider w-full" :min="cfg.storage.min" :max="cfg.storage.max"
                            :step="cfg.storage.step" v-model.number="storageGB" :style="sliderBg(storagePct)" />
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="vnx-input-box flex items-center gap-2 h-8 px-4 py-2 shrink-0">
                            <input type="number"
                                class="vnx-num font-semibold text-[16px] leading-6 text-[#282829] w-[60px] max-w-[130px]"
                                v-model.number="storageGB" @input="limitInput('storageGB', 7)"
                                @change="clamp('storageGB', cfg.storage.min, cfg.storage.max)" />
                            <span
                                class="text-[#747475] text-[16px] leading-6 whitespace-nowrap pl-2 border-l border-[#dedfe0]">{{ storageField.unit }}</span>
                        </div>
                        <span class="text-[#5b5b5c] text-[16px] leading-6 whitespace-nowrap">
                            x {{ storageTier.price || '—' }} VND/ {{ storageField.unit }}
                        </span>
                    </div>
                </div>
                <?php } elseif ($fileds['type'] == 'data_transfer') { ?>
                <!-- DATA TRANSFER -->
                <div class="flex flex-col gap-1 sm:p-4 p-3 rounded-[6px]" style="background:#fff">
                    <div class="flex items-center gap-4 w-full">
                        <div class="flex flex-1 items-center gap-2 font-semibold text-[16px] min-w-0 whitespace-nowrap">
                            <span class="leading-6 text-[#282829]"><?php echo $fileds['title']; ?></span>
                            <span
                                class="text-[#747475] text-[12px] leading-[22px]">(<?php echo $fileds['unit']; ?>)</span>
                        </div>
                        <p
                            class="font-bold sm:text-[16px] text-[12px] sm:leading-6 leading-[22px] text-[#282829] shrink-0 whitespace-nowrap">
                            {{ dtCost > 0 ? fmt(dtCost) + ' VND' : '0 VND' }}
                        </p>
                    </div>

                    <div class="vnx-slider-wrap">
                        <input type="range" class="vnx-slider w-full" :min="cfg.dt.min" :max="dtSliderMax"
                            :step="cfg.dt.step" v-model.number="dtTotalGB" :style="sliderBg(dtPct)" />
                    </div>

                    <div class="flex flex-wrap sm:items-center float-start sm:flex-row flex-col gap-3 justify-between">
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="vnx-input-box flex items-center gap-2 h-8 px-4">
                                <!-- <input type="number"
                                    class="vnx-num font-semibold text-[16px] leading-6 text-[#282829] w-[56px]"
                                    :min="cfg.dt.min" :max="cfg.dt.max" :step="cfg.dt.step" v-model.number="dtTotalGB"
                                    @change="clamp('dtTotalGB', dtFreeQuotaGB, cfg.dt.max)" /> -->

                                <input type="number"
                                    class="vnx-num font-semibold text-[16px] leading-6 text-[#282829] w-[60px]" min="0"
                                    :max="cfg.dt.max" :step="cfg.dt.step" v-model.number="dtTotalGB"
                                    @input="limitInput('dtTotalGB', 7)" @focus="isTypingDt = true"
                                    @blur="isTypingDt = false; clamp('dtTotalGB', dtFreeQuotaGB, cfg.dt.max)"
                                    @change="isTypingDt = false; clamp('dtTotalGB', dtFreeQuotaGB, cfg.dt.max)" />

                                <span
                                    class="text-[#747475] text-[16px] leading-6 pl-2 border-l border-[#dedfe0] whitespace-nowrap">{{ dtField.unit }}</span>
                            </div>
                            <span v-if="dtExceededGB > 0"
                                class="text-[#5b5b5c] text-[16px] leading-6 whitespace-nowrap">
                                x {{ dtTierPrice }} VND/ {{ dtField.unit }}
                            </span>
                        </div>
                        <div v-if="dtTotalGB <= dtFreeQuotaGB"
                            class="flex items-center gap-[6px] bg-[#dbffe4] px-2 rounded-full shrink-0 w-fit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16"
                                fill="none">
                                <path
                                    d="M7.99997 4.00025H6.66664V8.00023H1.33333C0.979707 8.00023 0.64057 7.85976 0.390523 7.60971C0.140475 7.35966 0 7.02052 0 6.6669V5.33358C0 4.97995 0.140475 4.64082 0.390523 4.39077C0.64057 4.14072 0.979707 4.00025 1.33333 4.00025H3.02399C2.69323 3.42751 2.58711 2.75229 2.72624 2.1057C2.86537 1.45912 3.23982 0.887305 3.77687 0.501297C4.31393 0.115289 4.97527 -0.0573689 5.63247 0.0168501C6.28968 0.0910691 6.89586 0.406868 7.3333 0.902927C7.77074 0.406868 8.37692 0.0910691 9.03413 0.0168501C9.69134 -0.0573689 10.3527 0.115289 10.8897 0.501297C11.4268 0.887305 11.8012 1.45912 11.9404 2.1057C12.0795 2.75229 11.9734 3.42751 11.6426 4.00025H13.3333C13.6869 4.00025 14.026 4.14072 14.2761 4.39077C14.5261 4.64082 14.6666 4.97995 14.6666 5.33358V6.6669C14.6666 7.02052 14.5261 7.35966 14.2761 7.60971C14.026 7.85976 13.6869 8.00023 13.3333 8.00023H7.99997V4.00025ZM3.99998 2.66692C3.99998 3.02054 4.14046 3.35968 4.39051 3.60972C4.64055 3.85977 4.97969 4.00025 5.33331 4.00025H6.66664V2.66692C6.66664 2.3133 6.52616 1.97416 6.27612 1.72411C6.02607 1.47407 5.68693 1.33359 5.33331 1.33359C4.97969 1.33359 4.64055 1.47407 4.39051 1.72411C4.14046 1.97416 3.99998 2.3133 3.99998 2.66692ZM7.99997 4.00025H9.33329C9.597 4.00025 9.85479 3.92205 10.0741 3.77554C10.2933 3.62903 10.4642 3.4208 10.5651 3.17716C10.666 2.93353 10.6924 2.66544 10.641 2.4068C10.5896 2.14816 10.4626 1.91058 10.2761 1.72411C10.0896 1.53765 9.85205 1.41066 9.59341 1.35921C9.33477 1.30776 9.06668 1.33417 8.82305 1.43509C8.57942 1.536 8.37118 1.7069 8.22467 1.92616C8.07816 2.14543 7.99997 2.40321 7.99997 2.66692V4.00025ZM13.3333 9.33356H7.99997V16.0002H10.6666C11.3739 16.0002 12.0521 15.7192 12.5522 15.2192C13.0523 14.7191 13.3333 14.0408 13.3333 13.3335V9.33356ZM6.66664 16.0002V9.33356H1.33333V13.3335C1.33333 14.0408 1.61428 14.7191 2.11437 15.2192C2.61447 15.7192 3.29274 16.0002 3.99998 16.0002H6.66664Z"
                                    fill="#00944A" />
                            </svg>
                            <span class="font-medium text-[14px] leading-6 text-[#282829] whitespace-nowrap">
                                Free quota: {{ fmt(dtFreeQuotaGB) }} {{ dtField.unit }}
                            </span>
                        </div>
                        <div v-else class="flex items-center px-2 rounded-full shrink-0 w-fit">
                            <span class="font-medium text-[14px] leading-6 text-[#C66000] ">
                                Đã vượt {{ fmt(dtExceededGB) }} {{ dtField.unit }} so với hạn mức miễn phí
                            </span>
                        </div>
                    </div>
                </div>
                <?php } else { ?>
                <!-- REQUEST -->
                <div class="flex flex-col gap-1 sm:p-4 p-3 rounded-[6px]" style="background:#fff">
                    <div class="flex items-center gap-4 w-full">
                        <div class="flex flex-1 items-center gap-2 font-semibold text-[16px] min-w-0 whitespace-nowrap">
                            <span class="leading-6 text-[#282829]"><?php echo $fileds['title']; ?></span>
                            <span
                                class="text-[#747475] text-[12px] leading-[22px]">(<?php echo $fileds['unit']; ?>)</span>
                        </div>
                        <p
                            class="font-bold sm:text-[16px] text-[12px] sm:leading-6 leading-[22px] text-[#282829] shrink-0 whitespace-nowrap">
                            {{ reqCost > 0 ? fmt(reqCost) + ' VND' : '0 VND' }}
                        </p>
                    </div>

                    <div class="vnx-slider-wrap">
                        <input type="range" class="vnx-slider w-full" :min="cfg.req.min" :max="cfg.req.max"
                            :step="cfg.req.step" v-model.number="reqTr" :style="sliderBg(reqPct)" />
                    </div>

                    <div class="flex flex-wrap sm:items-center float-start sm:flex-row flex-col gap-3 justify-between">
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="vnx-input-box flex items-center gap-2 h-8 px-4">
                                <input type="number"
                                    class="vnx-num font-semibold text-[16px] leading-6 text-[#282829] w-[68px]"
                                    step="0.01" :min="cfg.req.min" :max="cfg.req.max" v-model.number="reqTr"
                                    @input="limitInput('reqTr', 10)"
                                    @change="clamp('reqTr', cfg.req.min, cfg.req.max)" />
                                <span
                                    class="text-[#747475] text-[16px] leading-6 pl-2 border-l border-[#dedfe0] whitespace-nowrap">{{ reqField.unit }}</span>
                            </div>
                            <span v-if="reqExceeded > 0" class="text-[#5b5b5c] text-[16px] leading-6 whitespace-nowrap">
                                x {{ reqUnitPricePer1000 }} VND/ 1.000 req
                            </span>
                        </div>
                        <div v-if="reqExceeded <= 0"
                            class="flex items-center gap-[6px] bg-[#dbffe4] px-2 rounded-full shrink-0 w-fit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16"
                                fill="none">
                                <path
                                    d="M7.99997 4.00025H6.66664V8.00023H1.33333C0.979707 8.00023 0.64057 7.85976 0.390523 7.60971C0.140475 7.35966 0 7.02052 0 6.6669V5.33358C0 4.97995 0.140475 4.64082 0.390523 4.39077C0.64057 4.14072 0.979707 4.00025 1.33333 4.00025H3.02399C2.69323 3.42751 2.58711 2.75229 2.72624 2.1057C2.86537 1.45912 3.23982 0.887305 3.77687 0.501297C4.31393 0.115289 4.97527 -0.0573689 5.63247 0.0168501C6.28968 0.0910691 6.89586 0.406868 7.3333 0.902927C7.77074 0.406868 8.37692 0.0910691 9.03413 0.0168501C9.69134 -0.0573689 10.3527 0.115289 10.8897 0.501297C11.4268 0.887305 11.8012 1.45912 11.9404 2.1057C12.0795 2.75229 11.9734 3.42751 11.6426 4.00025H13.3333C13.6869 4.00025 14.026 4.14072 14.2761 4.39077C14.5261 4.64082 14.6666 4.97995 14.6666 5.33358V6.6669C14.6666 7.02052 14.5261 7.35966 14.2761 7.60971C14.026 7.85976 13.6869 8.00023 13.3333 8.00023H7.99997V4.00025ZM3.99998 2.66692C3.99998 3.02054 4.14046 3.35968 4.39051 3.60972C4.64055 3.85977 4.97969 4.00025 5.33331 4.00025H6.66664V2.66692C6.66664 2.3133 6.52616 1.97416 6.27612 1.72411C6.02607 1.47407 5.68693 1.33359 5.33331 1.33359C4.97969 1.33359 4.64055 1.47407 4.39051 1.72411C4.14046 1.97416 3.99998 2.3133 3.99998 2.66692ZM7.99997 4.00025H9.33329C9.597 4.00025 9.85479 3.92205 10.0741 3.77554C10.2933 3.62903 10.4642 3.4208 10.5651 3.17716C10.666 2.93353 10.6924 2.66544 10.641 2.4068C10.5896 2.14816 10.4626 1.91058 10.2761 1.72411C10.0896 1.53765 9.85205 1.41066 9.59341 1.35921C9.33477 1.30776 9.06668 1.33417 8.82305 1.43509C8.57942 1.536 8.37118 1.7069 8.22467 1.92616C8.07816 2.14543 7.99997 2.40321 7.99997 2.66692V4.00025ZM13.3333 9.33356H7.99997V16.0002H10.6666C11.3739 16.0002 12.0521 15.7192 12.5522 15.2192C13.0523 14.7191 13.3333 14.0408 13.3333 13.3335V9.33356ZM6.66664 16.0002V9.33356H1.33333V13.3335C1.33333 14.0408 1.61428 14.7191 2.11437 15.2192C2.61447 15.7192 3.29274 16.0002 3.99998 16.0002H6.66664Z"
                                    fill="#00944A" />
                            </svg>
                            <span class="font-medium text-[14px] leading-6 text-[#282829] whitespace-nowrap">
                                Free quota: {{ fmtDec(reqFreeQuota) }} triệu req/ tháng
                            </span>
                        </div>
                        <div v-else class="flex items-center px-2 rounded-full shrink-0 w-fit">
                            <span class="font-medium text-[14px] leading-6 text-[#C66000]">
                                Đã vượt {{ fmtDec(reqExceeded) }} triệu req so với hạn mức miễn phí
                            </span>
                        </div>
                    </div>
                </div>
                <?php }
                    }
                } ?>
            </div><!-- /resource blocks -->
        </div><!-- /LEFT PANEL -->

        <!-- RIGHT PANEL -->
        <div
            class="bg-[#fafafc] border border-[#dedfe0] rounded-xl flex flex-col lg:w-[340px] lg:flex-none w-full overflow-hidden sm:px-6 sm:pt-0 pb-5 px-4 ">

            <div class="border-b border-[#c0c0c2] py-4 shrink-0">
                <p class="font-bold sm:text-[18px] text-[14px] sm:leading-[30px] leading-6 text-[#282829]">
                    {{ rightHeading }}
                </p>
            </div>

            <div class="flex flex-col gap-3 pt-3">
                <!-- Storage -->
                <div class="flex justify-between items-start gap-[10px] w-full">
                    <div class="flex flex-nowrap flex-col min-w-[120px] w-full gap-1">
                        <div class=" flex flex-nowrap flex-row justify-between">
                            <p class="font-bold sm:text-[16px] sm:leading-6 text-[12px] leading-[22px] text-[#282829]">
                                {{ storageField.title }}
                            </p>
                            <p
                                class="font-bold sm:text-[16px] sm:leading-6 text-[12px] leading-[22px] text-[#282829] text-right break-words overflow-hidden">
                                <template v-if="storageTier.price">{{ fmt(storageCost) }} VND</template>
                                <template v-else><span class="text-[#007cfc] text-[14px]">Liên hệ</span></template>
                            </p>
                        </div>

                        <p class="sm:text-[14px] sm:leading-6 text-[12px] leading-[22px] text-[#282829] opacity-80">
                            {{ storageGB }}
                            {{ storageField.unit }} x
                            {{ storageTier.price || '—' }} VND/ {{ storageField.unit }}
                        </p>
                    </div>

                </div>
                <hr class="border-[#dedfe0]" />

                <!-- Data Transfer -->
                <div class="flex justify-between items-start gap-[10px] w-full">
                    <div class="flex flex-nowrap flex-col min-w-[120px] w-full gap-1">
                        <div class=" flex flex-nowrap flex-row justify-between">
                            <p class="font-bold sm:text-[16px] sm:leading-6 text-[12px] leading-[22px] text-[#282829]">
                                {{ dtField.title }}
                            </p>
                            <p
                                class="font-bold sm:text-[16px] sm:leading-6 text-[12px] leading-[22px] text-[#282829] text-right break-words overflow-hidden">
                                {{ dtCost > 0 ? fmt(dtCost) + ' VND' : '0 VND' }}
                            </p>
                        </div>
                        <p class="sm:text-[14px] sm:leading-6 text-[12px] leading-[22px] text-[#282829] opacity-80">
                            <span v-if="dtExceededGB <= 0">Free quota {{ fmt(dtFreeQuotaGB) }} {{ dtField.unit }}</span>
                            <span v-else>{{ fmt(dtExceededGB) }} {{ dtField.unit }} vượt x {{ dtTierPrice }} VND/
                                {{ dtField.unit }}</span>
                        </p>
                    </div>
                </div>
                <hr class="border-[#dedfe0]" />

                <!-- Request -->
                <div class="flex justify-between items-start gap-[10px] w-full">
                    <div class="flex flex-nowrap flex-col min-w-[120px] w-full gap-1">
                        <div class=" flex flex-nowrap flex-row justify-between">
                            <p class="font-bold sm:text-[16px] sm:leading-6 text-[12px] leading-[22px] text-[#282829]">
                                {{ reqField.title }}
                            </p>
                            <p
                                class="font-bold sm:text-[16px] sm:leading-6 text-[12px] leading-[22px] text-[#282829] text-right break-words overflow-hidden">
                                {{ reqCost > 0 ? fmt(reqCost) + ' VND' : '0 VND' }}
                            </p>
                        </div>
                        <p class="sm:text-[14px] sm:leading-6 text-[12px] leading-[22px] text-[#282829] opacity-80">
                            <span v-if="reqCost <= 0">Free quota {{ fmtDec(reqFreeQuota) }} tr req</span>
                            <span v-else>{{ fmtDec(reqExceeded) }} tr req vượt x {{ reqUnitPricePer1000 }} VND/ 1.000
                                req</span>
                        </p>
                    </div>
                </div>
            </div><!-- /price rows -->
            <!-- Total + CTA -->
            <div class=" flex flex-col gap-3 pt-3">
                <div class="border-t border-[#c0c0c2] flex items-start gap-[10px] pt-3">
                    <div class="flex-1 min-w-0 w-full">
                        <div class=" flex flex-nowrap flex-row justify-between">
                            <p class="font-bold sm:text-[16px] sm:leading-6 text-[12px] leading-[22px] text-[#282829]">
                                Tổng
                                cộng</p>
                            <p
                                class="font-bold sm:text-[16px] sm:leading-[24px] text-[12px] leading-[22px] text-[#282829] text-right break-words overflow-hidden">
                                <template v-if="storageTier.price">{{ fmt(totalCost) }} VND</template>
                                <template v-else><span class="text-[#007cfc] text-[16px]">Liên hệ</span></template>
                            </p>
                        </div>
                        <p class="sm:text-[14px] sm:leading-6 text-[12px] leading-[22px] text-[#282829]">
                            {{ selectedCycle.label }}
                            <template v-if="selectedCycle.discount > 0">
                                · Tiết kiệm {{ selectedCycle.discount }}%
                            </template>
                        </p>
                        <p class="sm:text-[14px] sm:leading-6 text-[10px] leading-[20px] text-[#282829]">{{ vatNote }}
                        </p>
                    </div>

                </div>

                <!-- Btn Đăng ký: ẩn khi tier = contact -->
                <button v-if="!isContactTier" @click="orderProduct" :disabled="isLoading"
                    class="bg-[#007cfc] disabled:opacity-50 disabled:cursor-not-allowed flex gap-2 items-center justify-center px-8 py-3 rounded-lg w-full hover:bg-[#085fc5] transition-colors duration-200">
                    <svg v-if="isLoading" class="animate-spin h-5 w-5 text-[#FCFCFC] shrink-0"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span
                        class="font-medium sm:text-[18px] text-[14px] sm:leading-[30px] leading-[24px] text-[#FCFCFC] whitespace-nowrap">{{ ctaLabel }}</span>
                </button>

                <!-- Btn Liên hệ: hiện khi tier = contact -->
                <button v-if="isContactTier" @click="orderContact"
                    class="bg-[#007cfc] btn_tawk flex gap-2 items-center justify-center px-8 py-3 rounded-lg w-full hover:bg-[#085fc5] transition-colors duration-200">
                    <span
                        class="font-medium sm:text-[18px] text-[14px] sm:leading-[30px] leading-[24px] text-[#FCFCFC] whitespace-nowrap">{{ ctaContactLabel }}</span>
                </button>
            </div>

        </div><!-- /RIGHT PANEL -->
    </div><!-- /MAIN CARD -->
</div><!-- /vnx-obj-v2 -->