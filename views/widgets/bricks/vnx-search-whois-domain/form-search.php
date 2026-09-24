<?php
if (!defined('ABSPATH')) exit;
$data = isset($data) ? $data : new stdClass();
$data->set_attribute('_root', 'class', 'vnx-search-whois-domain');
$settings = $data->settings;

$isRedirect = $settings['is-redirect'] ?? false;
$linkRedirect = $settings['link-redirect'] ?? '';
?>

<script>
    window.isRedirect = <?php echo json_encode($isRedirect); ?>;
    window.linkRedirect = <?php echo json_encode($linkRedirect); ?>;
</script>


<div <?php echo $data->render_attributes('_root'); ?>>
    <div class="bg-white rounded-xl p-1 relative w-[800px] vnx_tablet:w-full h-[52px] flex vnx_tablet:flex-col vnx_tablet:h-[100px]">

        <label class="absolute left-3 vnx_tablet:left-5 top-[17px] vnx_tablet:top-[15px]" for="vnx-input-domain">
            <i class="fa-light fa-magnifying-glass  text-[#747475]"></i>
        </label>

        <input
            id="vnx-input-domain"
            v-model="domain"
            type="text"
            class="w-full h-full text-[#282829] border-none pl-[35px] vnx_tablet:pl-[40px] vnx_tablet:mb-3 text-[18px] vnx_tablet:text-[14px] font-normal"
            placeholder="Nhập tên miền cần tra cứu"
            @keyup.enter="searchDomain"
            :disabled="isLoading">

        <button id="vnx-button-search" @click="searchDomain"
            :disabled="isLoading"
            class="flex flex-none justify-center items-center w-[200px] vnx_tablet:w-full  rounded-lg h-full vnx_tablet:h-[40px] text-[#FCFCFC] font-inter text-[18px] vnx_tablet:text-[16px] vnx_tablet:font-normal vnx_tablet:leading-[24px] font-semibold leading-[30px] disabled:opacity-50"
            style="background: linear-gradient(104deg, #FF9400 0%, #FFC500 100%)">

            <span v-cloak  v-if="isLoading" class="flex"><svg class="animate-[spin_0.5s_linear_infinite] h-5 w-5 mr-2 text-white "
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </span>


            <span>
                Kiểm tra Whois
            </span>
        </button>
    </div>
</div>