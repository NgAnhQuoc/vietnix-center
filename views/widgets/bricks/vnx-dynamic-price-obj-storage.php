<?php $settings = $data->settings; ?>
   <div class="vnx-dynamic-price-obj-storage w-full" v-cloak data-settings='<?php echo json_encode($settings); ?>'>
       <div class="flex flex-col gap-4">


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


           <!-- Top section  -->
           <div>
               <div class="flex gap-3">
                   <!-- Left  -->
                   <div class="border border-[#007CFC] rounded-lg p-5 pt-4 w-full vnx_tablet:p-3 max-h-[130px] bg-white">
                       <span class="vnx-font-base text-[#000000]">
                           <?php echo $settings['title']; ?>
                       </span>

                       <div>
                           <div class="range-slider">

                               <input type="range" min="0" :max="resource.max - resource.min" :step="resource.step" v-model="resource.value" class="slider" :id="`${resource.key}_range`">
                               <div class="slider-thumb">
                                   <div class="tooltip">{{ Number(resource.value) + Number(resource.min) }}</div>
                               </div>
                               <div class="progress"></div>
                           </div>
                           <div class="w-full text-[#000000] flex justify-between text-base vnx_tablet:text-[12px] left-[22px] leading-6">
                               <span class="ml-2">{{ Number(resource.min) }}</span>
                               <span>{{ Number(resource.max) }}</span>
                           </div>
                       </div>
                   </div>
                   <!-- Right  -->
                   <div class="border border-[#007CFC] text-[#007CFC] rounded-lg flex justify-center items-center flex-col gap-3 max-w-[240px] w-[240px] h-[130px] flex-none overflow-visible vnx_tablet:hidden bg-white">

                       <!-- Xử lý input button  -->
                       <div class="border border-[#C0C0C2] rounded flex">
                           <button @click="decrement()"
                               :disabled="Number(resource.value) === 0"
                               class="disabled:opacity-20 size-10 flex-none vnx_tablet:size-7 border border-y-0 border-l-0 border-[#C0C0C2] text-[#282829]">-</button>

                           <input
                               type="number"
                               min="0"
                               class="price-amount border-none px-0 font-bold text-[28px] w-[120px] text-center leading-[40px]"
                               :value="Number(resource.value) + Number(resource.min)"
                               @change="onchangeInput($event)"
                               @keypress.enter="onchangeInput($event)"
                               onkeypress="return (event.charCode >= 48 && event.charCode <= 57)" />


                           <button @click="increment()"
                               :disabled="Number(resource.value) >= Number(resource.max) - Number(resource.min)"
                               class="disabled:opacity-20 flex-none  size-10 vnx_tablet:size-7 border border-y-0 border-r-0 border-[#C0C0C2] text-[#282829]">+</button>
                       </div>
                       <span class="text-center text-[18px] leading-[30px]">
                           {{ resource.unit }} đã chọn
                       </span>
                   </div>
               </div>
           </div>

           <!-- Bottom section  -->
           <div class="border border-[#007CFC] rounded-lg p-5 w-full flex justify-between items-center vnx_tablet:hidden bg-white">
               <!-- left  -->
               <div class="font-bold vnx-font-base text-[#282829]  flex items-center  gap-[10px]">
                   <span>Tổng tiền:</span>
                   <span>{{ formatCurrency(totalBill) }} /tháng</span>
                   <span class="font-normal text-[14px] leading-6">(Chưa bao gồm VAT)</span>
               </div>
               <!-- right  -->
               <div>
                   <button :disabled="isLoading" @click="orderProduct" class=" disabled:opacity-50 disabled:cursor-not-allowed flex px-8 py-3 justify-center items-center gap-2 rounded-full bg-[#007CFC] hover:bg-[#085FC5] text-[#FCFCFC] text-[18px] font-medium left-[30]">
                       <span v-if="isLoading" class="flex"><svg class="animate-[spin_0.5s_linear_infinite] -ml-1 mr-3 h-5 w-5 text-white "
                               xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                               <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                               <path class="opacity-75" fill="currentColor"
                                   d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                               </path>
                           </svg></span>
                       Đăng ký ngay
                       </span> <i class="fa-solid fa-arrow-right"></i></button>
               </div>
           </div>

           <!-- Bottom section mobile  -->
           <div class="border border-[#007CFC] rounded-lg p-3 w-full  flex-col hidden vnx_tablet:flex bg-white">
                   <div class="vnx-font-base text-[#282829]  flex flex-col gap-3 mb-3">
                       <div class="flex items-center vnx-font-base  gap-2">
                           <span class="text-center">
                               Đã chọn ({{ resource?.unit || "" }})
                           </span>

                           <div class="border border-[#C0C0C2] rounded flex">
                               <button @click="decrement()"
                                   :disabled="Number(resource.value) === 0"
                                   class="disabled:opacity-20 size-10 flex-none vnx_tablet:size-7 border border-y-0 border-l-0 border-[#C0C0C2] text-[#282829]">-</button>

                               <input
                                   type="number"
                                   min="0"
                                   class="price-amount border-none px-0 font-bold text-[14px] w-[60px] text-center leading-[24px] text-[#007CFC]"
                                   :value="Number(resource.value) + Number(resource.min)"
                                   @change="onchangeInput($event)"
                                   @keypress.enter="onchangeInput($event)"
                                   onkeypress="return (event.charCode >= 48 && event.charCode <= 57)" />


                               <button @click="increment()"
                                   :disabled="Number(resource.value) >= Number(resource.max) - Number(resource.min)"
                                   class="disabled:opacity-20 flex-none  size-10 vnx_tablet:size-7 border border-y-0 border-r-0 border-[#C0C0C2] text-[#282829]">+</button>
                           </div>
                       </div>


                       <div class="flex justify-between">
                           <div>
                               <div class="font-bold">Tổng cộng</div>
                               <div class="">Chưa bao gồm VAT</div>
                           </div>
                           <div class="font-bold text-[14px] leading-6 text-[#282829]">{{ formatCurrency(totalBill) }} /Tháng</div>
                       </div>
                   </div>

               <button @click="orderProduct" :disabled="isLoading" class=" disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center vnx-font-base h-[40px] gap-2 rounded-lg bg-[#007CFC] hover:bg-[#085FC5] text-[#FCFCFC] text-[18px] font-medium left-[30]">
                   <span v-if="isLoading" class="flex"><svg class="animate-[spin_0.5s_linear_infinite] -ml-1 mr-3 h-5 w-5 text-white "
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