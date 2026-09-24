<?php
if (!defined('ABSPATH')) exit;
$data = isset($data) ? $data : new stdClass();
$data->set_attribute('_root', 'class', 'vnx-suggest-slide-whois-domain w-full');
$settings = $data->settings;
$list_tld = $settings['list-tld'] ? explode(',', str_replace(' ', '', $settings['list-tld'])) : [];
?>

<script>
    window.listTLD = <?php echo json_encode($data->dataCSV('import-csv')); ?>;
</script>

<div v-cloak <?php echo $data->render_attributes('_root'); ?>>
    <!-- Loading state -->
    <div class="flex gap-4 max-h-[400px] min-h-[85px] overflow-hidden relative vnx-sidebar-xscroll min-w-28">

        <!-- loading  -->
        <div v-if="isLoading" class="absolute w-full h-full w-full flex justify-center items-center opacity-40 bg-white border border-gray-300 rounded z-10">
            <span v-if="true" class="flex"><svg class="animate-[spin_0.4s_linear_infinite] size-9 mr-2 text-[#008cff] z-11"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </span>
        </div>

        <!-- Domain list -->
        <div
            class="flex flex-col justify-between min-w-[240px] vnx_tablet:min-w-[200px] bg-[#FAFAFC] rounded border border-[#C0C0C2] py-1 px-2"
            v-else-if="listResultDomain && listResultDomain.length > 0"
            v-for="domain in listResultDomain"
            :key="domain.domainName">

            <div class="text-[16px] vnx_tablet:text-[14px] leading-6 font-bold flex">
                <span class="text-[#282829]">{{ domain.sld }}</span>
                <span class="text-[#007CFC]">.{{ domain.tld }}</span>
            </div>
            <div v-if="domain.isAvailable && domain.pricing" class="relative">
                <div 
                v-if="Number(getPriceOriginal(domain.tld)) > 0
                && domain.pricing
                && domain.pricing.register
                && domain.pricing.register['1']
                && Number(getPriceOriginal(domain.tld)) > Number(domain.pricing.register['1'])" 
                class="line-through text-[#282829] text-[12px]" >{{formatVND(getPriceOriginal(domain.tld)) }}đ</div>
                <div class="text-[14px] leading-[24px] text-[#FF6F00] font-bold left-6">{{ formatVND(domain.pricing.register['1']) }}đ</div>
                <button @click="orderProduct(domain.domainName)" class="absolute p-1 bottom-0 right-0 text-white size-[32px] flex justify-center items-center bg-[#007CFC] rounded-full">
                    <i class="fa-regular fa-cart-plus"></i>
                </button>
            </div>
        </div>
    </div>
</div>