<?php
if (!defined('ABSPATH'))
    exit;
$data = isset($data) ? $data : new stdClass();
$data->set_attribute('_root', 'class', 'vnx-show-result-whois-domain');
$settings = $data->settings;


$extra_services = $settings['extra-services'] ?? [];
$domain_combos = $settings['domain-combos'] ?? [];



// echo json_encode($data->getFileCSV('import-csv-domain-0d, 2'));
// [
//   [
//     "com",
//     "0đ năm đầu | | 1.205.000đ ",
//     "0đ năm đầu | 1.350.000đ | 1.205.000đ | Link ảnh từ website",
//     "0đ năm đầu | 1.350.000đ | 1.205.000đ | Link ảnh từ website"
//   ],
//   [
//     "pro.vn",
//     "0đ năm đầu | | 1.205.000đ ",
//     "0đ năm đầu | 1.350.000đ | 1.205.000đ | Link ảnh từ website",
//     "0đ năm đầu | 1.350.000đ | 1.205.000đ | Link ảnh từ website"
//   ],
//   [
//     "id.vn",
//     "0đ năm đầu | | 1.205.000đ ",
//     "0đ năm đầu | 1.350.000đ | 1.205.000đ | Link ảnh từ website",
//     "0đ năm đầu | 1.350.000đ | 1.205.000đ | Link ảnh từ website"
//   ],
//   [
//     "com",
//     "0đ năm đầu | | 1.205.000đ ",
//     "0đ năm đầu | 1.350.000đ | 1.205.000đ | Link ảnh từ website",
//     "0đ năm đầu | 1.350.000đ | 1.205.000đ | Link ảnh từ website"
//   ],
//   [
//     "name.vn",
//     "0đ năm đầu | | 1.205.000đ ",
//     "0đ năm đầu | 1.350.000đ | 1.205.000đ | Link ảnh từ website",
//     "0đ năm đầu | 1.350.000đ | 1.205.000đ | Link ảnh từ website"
//   ]
// ]
?>
<script>
    window.extraServices = <?php echo json_encode($extra_services); ?>;
    window.domainCombos = <?php echo json_encode($domain_combos); ?>;
    window.listTLD2 = <?php echo json_encode($data->dataCSV('import-csv')); ?>;
    window.listPriceDomain0d = <?php echo json_encode($data->getFileCSV('import-csv-domain-0d', 2)); ?>;
    window.listPriceDomainCombo = <?php echo json_encode($data->getFileCSV('import-csv-domain-combo', 2)); ?>;
</script>
<div v-cloak <?php echo $data->render_attributes('_root'); ?>>


    <!-- Màn hình ban đầu -->
    <div v-if="firstScreen" class="text-[#282829] w-full flex flex-col center py-10 gap-6 mb-8">
        <img width="120" height="120" src="https://image.vietnix.vn/wp-content/uploads/2025/11/image-4.webp" alt="">

        <div class="text-[18px] leading-[30px] vnx_tablet:text-[14px] vnx_tablet:leading-6">
            Tên miền không hợp lệ. Vui lòng kiểm tra lại.
        </div>
    </div>
    <!--end Màn hình ban dầu -->



    <div v-if="resultAvailableDomain" class="relative">
        <!-- loading  -->
        <div v-if="isLoading" class="absolute w-full h-full flex justify-center items-center opacity-40 bg-white z-10">
            <span class="flex"><svg class="animate-[spin_0.4s_linear_infinite] size-9 mr-2 text-[#008cff] z-11"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </span>
        </div>


        <!-- Hiển thị kết quả không phù hợp  -->
        <div v-if="resultWhoisDomain != null && resultWhoisDomain?.domainStatus === 'undefined' && !resultAvailableDomain.isAvailable"
            class="text-[#282829] w-full flex flex-col center py-10 gap-6 mb-8 min-h-[254px]"
            style="min-height: 254px;">
            <img v-if="!isLoading" width="120" height="120"
                src="https://image.vietnix.vn/wp-content/uploads/2025/11/image-4.webp" alt="">

            <div v-if="!isLoading" class="text-[18px] leading-[30px] vnx_tablet:text-[14px] vnx_tablet:leading-6">
                Tên miền không hợp lệ. Vui lòng kiểm tra lại.
            </div>
        </div>
        <!--end Hiển thị kết quả không phù hợp  -->

        <!-- Hiển thị khi tên miền được nhà nước/VNNIC bảo mật, không lấy được whois -->
        <div v-if="resultWhoisDomain != null && resultWhoisDomain?.domainStatus === 'reserved' && !resultAvailableDomain.isAvailable"
            class="text-[#282829] w-full flex flex-col center py-10 gap-6 mb-8 min-h-[254px]"
            style="min-height: 254px;">
            <img v-if="!isLoading" width="120" height="120"
                src="https://image.vietnix.vn/wp-content/uploads/2025/11/image-4.webp" alt="">

            <div v-if="!isLoading" class="text-[18px] leading-[30px] vnx_tablet:text-[14px] vnx_tablet:leading-6">
                Không thể lấy được thông tin tên miền này. Vui lòng thử lại với tên miền khác.
            </div>
        </div>
        <!--end Hiển thị tên miền được bảo mật -->

        <!-- Hiển thị kết quả nếu có whois -->
        <div v-if="resultWhoisDomain && !resultAvailableDomain.isAvailable && resultWhoisDomain?.domainStatus != 'undefined' && resultWhoisDomain?.domainStatus != 'reserved'"
            class="text-[#282829] mb-8 vnx_tablet:mb-6">
            <div
                class="text-[24px] vnx_tablet:text-[20px] vnx_tablet:leading-8 font-bold leading-9 vnx_tablet:mb-3 mb-4">
                Domain: {{ resultWhoisDomain?.domain ?? '' }}
            </div>

            <!-- Hiển thị kết quả whois VN -->
            <div v-if="resultWhoisDomain.domain_type === 'vn'"
                class="border border-[#C0C0C2] rounded mb-8 vnx_tablet:mb-6">
                <p
                    class="text-[18px] leading-[30px] vnx_tablet:text-[14px] vnx_tablet:leading-6 font-bold left-8 px-5 py-2 vnx_tablet:px-2 vnx_tablet:py-1 bg-[#F2F3F5]">
                    Thông tin tên miền
                </p>

                <div
                    class="p-5 vnx_tablet:p-2 py-4 text-[16px] vnx_tablet:text-[12px] leading-6 vnx_tablet:leading-[22px] rounded">
                    <div class="space-y-4 vnx_tablet:space-y-2">
                        <div class="flex gap-3" v-if="hasValue(resultWhoisDomain?.domain)">
                            <div class="w-[240px] vnx_tablet:w-[103px]">Tên miền</div>
                            <div>{{ resultWhoisDomain?.domain ?? '' }}</div>
                        </div>

                        <div class="flex gap-3" v-if="hasValue(resultWhoisDomain?.domainName)">
                            <div class="w-[240px] flex-none  vnx_tablet:w-[103px]">Chủ thể đăng ký sử dụng</div>
                            <div>{{ resultWhoisDomain?.domainName ?? '' }}</div>
                        </div>

                        <div class="flex gap-3" v-if="hasValue(resultWhoisDomain?.registrarName)">
                            <div class="w-[240px] flex-none  vnx_tablet:w-[103px]">Nhà đăng ký quản lý:</div>
                            <div>{{ resultWhoisDomain?.registrarName ?? '' }}</div>
                        </div>

                        <div class="flex gap-3" v-if="hasValue(resultWhoisDomain?.creationDate)">
                            <div class="w-[240px] flex-none  vnx_tablet:w-[103px]">Ngày đăng ký:</div>
                            <div>{{ formatDate(resultWhoisDomain?.creationDate) }}</div>
                        </div>

                        <div class="flex gap-3" v-if="hasValue(resultWhoisDomain?.expirationDate)">
                            <div class="w-[240px] flex-none  vnx_tablet:w-[103px]">Ngày hết hạn:</div>
                            <div>{{ formatDate(resultWhoisDomain?.expirationDate) }}</div>
                        </div>

                        <div class="flex gap-3" v-if="hasValue(resultWhoisDomain?.domainStatus)">
                            <div class="w-[240px] flex-none  vnx_tablet:w-[103px]">Trạng thái</div>
                            <div>{{ resultWhoisDomain?.domainStatus ?? '' }}</div>
                        </div>
                        <div class="flex gap-3" v-if="hasValue(resultWhoisDomain?.nameServers)">
                            <div class="w-[240px] flex-none vnx_tablet:w-[103px]">Máy chủ DNS chuyển giao</div>
                            <div>
                                <span v-for="(ns, idx) in resultWhoisDomain.nameServers.split(',')" :key="idx">
                                    {{ ns.trim() }}<br>
                                </span>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="w-[240px] flex-none  vnx_tablet:w-[103px]">DNSSEC</div>
                            <div>{{ hasValue(resultWhoisDomain?.dnssec) ? resultWhoisDomain.dnssec : 'Chưa ký' }}</div>
                        </div>
                        <!-- các dòng khác tương tự -->
                    </div>
                </div>
            </div>

            <!-- Hiển thị kết quả whois QT -->
            <div v-else-if="resultWhoisDomain.domain_type === 'qt'"
                class="grid grid-cols-2 vnx_tablet:grid-cols-1 gap-x-6 vnx_tablet:gap-y-3 mb-8 vnx_tablet:mb-6">
                <!-- Cột thông tin Bên trái  -->
                <div class="space-y-8 vnx_tablet:space-y-3">
                    <!-- Domain Information -->
                    <div class="border border-[#C0C0C2] rounded" v-if="hasAnyValue(resultWhoisDomain?.domain, resultWhoisDomain?.creationDate, resultWhoisDomain?.expirationDate, resultWhoisDomain?.updatedDate, resultWhoisDomain?.domainStatus, resultWhoisDomain['Name Server'])">
                        <p
                            class="text-[18px] leading-[30px] vnx_tablet:text-[14px] vnx_tablet:leading-6 font-bold left-8 px-5 py-2 vnx_tablet:px-2 vnx_tablet:py-1 bg-[#F2F3F5]">
                            Domain Information
                        </p>
                        <div
                            class="p-5 vnx_tablet:p-2 py-4 text-[16px] vnx_tablet:text-[12px] leading-6 vnx_tablet:leading-[22px] rounded">
                            <div class="space-y-4 vnx_tablet:space-y-2">
                                <div class="flex" v-if="hasValue(resultWhoisDomain?.domain)">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Domain</div>
                                    <div>{{ resultWhoisDomain?.domain ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain?.creationDate)">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Registered On</div>
                                    <div>{{ formatDate(resultWhoisDomain?.creationDate) }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain?.expirationDate)">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Expires On</div>
                                    <div>{{ formatDate(resultWhoisDomain?.expirationDate) }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain?.updatedDate)">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Updated On</div>
                                    <div>{{ formatDate(resultWhoisDomain?.updatedDate) }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain?.domainStatus)">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Status</div>
                                    <div>
                                        <span v-for="(status, idx) in resultWhoisDomain.domainStatus.split(',')"
                                            :key="idx">
                                            {{ status.trim() }}<br>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Name Server'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Name Servers</div>
                                    <div>{{ resultWhoisDomain["Name Server"] ?? '' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Registrar Information -->
                    <div class="border border-[#C0C0C2] rounded" v-if="hasAnyValue(resultWhoisDomain?.Registrar, resultWhoisDomain['Registrar IANA ID'], resultWhoisDomain['Registrar Abuse Contact Email'], resultWhoisDomain['Registrar Abuse Contact Phone'])">
                        <p
                            class="text-[18px] leading-[30px] vnx_tablet:text-[14px] vnx_tablet:leading-6 font-bold left-8 px-5 py-2 vnx_tablet:px-2 vnx_tablet:py-1 bg-[#F2F3F5]">
                            Registrar Information
                        </p>
                        <div
                            class="p-5 vnx_tablet:p-2 py-4 text-[16px] vnx_tablet:text-[12px] leading-6 vnx_tablet:leading-[22px] rounded">
                            <div class="space-y-4 vnx_tablet:space-y-2">
                                <div class="flex" v-if="hasValue(resultWhoisDomain?.Registrar)">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Registrar</div>
                                    <div>{{ resultWhoisDomain?.Registrar ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrar IANA ID'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">IANA ID</div>
                                    <div>{{ resultWhoisDomain["Registrar IANA ID"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrar Abuse Contact Email'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Abuse Email</div>
                                    <div v-html="resultWhoisDomain['Registrar Abuse Contact Email'] ?? ''"></div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrar Abuse Contact Phone'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Abuse Phone</div>
                                    <div>{{ resultWhoisDomain["Registrar Abuse Contact Phone"] ?? '' }}</div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- Registrant Contact -->
                    <div class="border border-[#C0C0C2] rounded" v-if="hasAnyValue(resultWhoisDomain['Registrant Name'], resultWhoisDomain['Registrant Street'], resultWhoisDomain['Registrant City'], resultWhoisDomain['Registrant Postal Code'], resultWhoisDomain['Registrant Country'], resultWhoisDomain['Registrant Phone'], resultWhoisDomain['Registrant Email'])">
                        <p
                            class="text-[18px] leading-[30px] vnx_tablet:text-[14px] vnx_tablet:leading-6 font-bold left-8 px-5 py-2 vnx_tablet:px-2 vnx_tablet:py-1 bg-[#F2F3F5]">
                            Registrant Contact
                        </p>
                        <div
                            class="p-5 vnx_tablet:p-2 py-4 text-[16px] vnx_tablet:text-[12px] leading-6 vnx_tablet:leading-[22px] rounded">
                            <div class="space-y-4 vnx_tablet:space-y-2">
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Name'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Name</div>
                                    <div>{{ resultWhoisDomain["Registrant Name"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Street'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Street</div>
                                    <div>{{ resultWhoisDomain["Registrant Street"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant City'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">City</div>
                                    <div>{{ resultWhoisDomain["Registrant City"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Postal Code'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Postal Code</div>
                                    <div>{{ resultWhoisDomain["Registrant Postal Code"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Country'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Country</div>
                                    <div>{{ resultWhoisDomain["Registrant Country"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Phone'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Phone</div>
                                    <div>{{ resultWhoisDomain["Registrant Phone"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Email'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Email</div>
                                    <div class="break-all" v-html="resultWhoisDomain['Registrant Email'] ?? ''"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột thông tin Bên phải  -->
                <div class="space-y-8 vnx_tablet:space-y-3">
                    <!-- Administrative Contact -->
                    <div class="border border-[#C0C0C2] rounded" v-if="hasAnyValue(resultWhoisDomain['Admin Name'], resultWhoisDomain['Admin Street'], resultWhoisDomain['Admin City'], resultWhoisDomain['Admin Postal Code'], resultWhoisDomain['Admin Country'], resultWhoisDomain['Admin Phone'], resultWhoisDomain['Admin Email'])">
                        <p
                            class="text-[18px] leading-[30px] vnx_tablet:text-[14px] vnx_tablet:leading-6 font-bold left-8 px-5 py-2 vnx_tablet:px-2 vnx_tablet:py-1 bg-[#F2F3F5]">
                            Administrative Contact
                        </p>
                        <div
                            class="p-5 vnx_tablet:p-2 py-4 text-[16px] vnx_tablet:text-[12px] leading-6 vnx_tablet:leading-[22px] rounded">
                            <div class="space-y-4 vnx_tablet:space-y-2">
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Admin Name'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Name</div>
                                    <div>{{ resultWhoisDomain["Admin Name"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Admin Street'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Street</div>
                                    <div>{{ resultWhoisDomain["Admin Street"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Admin City'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">City</div>
                                    <div>{{ resultWhoisDomain["Admin City"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Admin Postal Code'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Postal Code</div>
                                    <div>{{ resultWhoisDomain["Admin Postal Code"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Admin Country'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Country</div>
                                    <div>{{ resultWhoisDomain["Admin Country"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Admin Phone'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Phone</div>
                                    <div>{{ resultWhoisDomain["Admin Phone"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Admin Email'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Email</div>
                                    <div class="break-all" v-html="resultWhoisDomain['Admin Email'] ?? ''"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Technical Contact -->
                    <div class="border border-[#C0C0C2] rounded" v-if="hasAnyValue(resultWhoisDomain['Tech Name'], resultWhoisDomain['Tech Street'], resultWhoisDomain['Tech City'], resultWhoisDomain['Tech Postal Code'], resultWhoisDomain['Tech Country'], resultWhoisDomain['Tech Phone'], resultWhoisDomain['Tech Email'])">
                        <p
                            class="text-[18px] leading-[30px] vnx_tablet:text-[14px] vnx_tablet:leading-6 font-bold left-8 px-5 py-2 vnx_tablet:px-2 vnx_tablet:py-1 bg-[#F2F3F5]">
                            Technical Contact
                        </p>
                        <div
                            class="p-5 vnx_tablet:p-2 py-4 text-[16px] vnx_tablet:text-[12px] leading-6 vnx_tablet:leading-[22px] rounded">
                            <div class="space-y-4 vnx_tablet:space-y-2">
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Tech Name'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Name</div>
                                    <div>{{ resultWhoisDomain["Tech Name"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Tech Street'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Street</div>
                                    <div>{{ resultWhoisDomain["Tech Street"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Tech City'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">City</div>
                                    <div>{{ resultWhoisDomain["Tech City"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Tech Postal Code'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Postal Code</div>
                                    <div>{{ resultWhoisDomain["Tech Postal Code"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Tech Country'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Country</div>
                                    <div>{{ resultWhoisDomain["Tech Country"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Tech Phone'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Phone</div>
                                    <div>{{ resultWhoisDomain["Tech Phone"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Tech Email'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Email</div>
                                    <div class="break-all" v-html="resultWhoisDomain['Tech Email'] ?? ''"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Billing Contact -->
                    <div class="border border-[#C0C0C2] rounded" v-if="hasAnyValue(resultWhoisDomain['Registrant Name'], resultWhoisDomain['Registrant Street'], resultWhoisDomain['Registrant City'], resultWhoisDomain['Registrant Postal Code'], resultWhoisDomain['Registrant Country'], resultWhoisDomain['Registrant Phone'], resultWhoisDomain['Registrant Email'])">
                        <p
                            class="text-[18px] leading-[30px] vnx_tablet:text-[14px] vnx_tablet:leading-6 font-bold left-8 px-5 py-2 vnx_tablet:px-2 vnx_tablet:py-1 bg-[#F2F3F5]">
                            Billing Contact
                        </p>
                        <div
                            class="p-5 vnx_tablet:p-2 py-4 text-[16px] vnx_tablet:text-[12px] leading-6 vnx_tablet:leading-[22px] rounded">
                            <div class="space-y-4 vnx_tablet:space-y-2">
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Name'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Name</div>
                                    <div>{{ resultWhoisDomain["Registrant Name"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Street'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Street</div>
                                    <div>{{ resultWhoisDomain["Registrant Street"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant City'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">City</div>
                                    <div>{{ resultWhoisDomain["Registrant City"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Postal Code'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Postal Code</div>
                                    <div>{{ resultWhoisDomain["Registrant Postal Code"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Country'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Country</div>
                                    <div>{{ resultWhoisDomain["Registrant Country"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Phone'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Phone</div>
                                    <div>{{ resultWhoisDomain["Registrant Phone"] ?? '' }}</div>
                                </div>
                                <div class="flex" v-if="hasValue(resultWhoisDomain['Registrant Email'])">
                                    <div class="min-w-[240px] vnx_tablet:min-w-[103px]">Email</div>
                                    <div class="break-all" v-html="resultWhoisDomain['Registrant Email'] ?? ''"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end Hiển thị kết quả nếu có whois -->



        <!-- Hiển thị kết quả Đăng ký domain -->
        <div v-if="resultAvailableDomain.isAvailable">

            <p
                class="text-[#282829] font-bold text-[24px] leading-[36px] vnx_tablet:text-[20px] vnx_tablet:leading-[32px] mb-4 vnx_tablet:mb-3">
                Domain {{ resultAvailableDomain.domainName }}
            </p>


            <div class="border border-[#C0C0C2] rounded mb-8 vnx_tablet:mb-6">
                <div class="text-white text-[18px] vnx_tablet:text-[14px] vnx_tablet:leading-6 font-bold left-8 px-5 py-2 vnx_tablet:px-2 vnx_tablet:py-1 rounded-t"
                    style="background: linear-gradient(90deg, #007CFC 0%, #0064CC 100%);">
                    Tên miền này chưa được đăng ký
                </div>

                <!-- Tên miền  -->
                <div class="p-5 pt-4 vnx_tablet:p-2 vnx_tablet:pt-2 vnx_tablet:pb-3 text-[#282829] leading-[30px]">
                    <p class="text-[18px] vnx_tablet:text-[14px] leading-[30px] vnx_tablet:leading-6 mb-3">
                        Đăng ký ngay tên miền để bảo vệ thương hiệu của bạn
                    </p>


                    <div class="flex items-center">
                        <div class="flex vnx_tablet:flex-col items-center vnx_tablet:items-start justify-start w-full">
                            <span
                                class="text-[20px] leading-[30px] vnx_tablet:leading-[24px] text-[#007CFC] font-bold vnx_tablet:text-[18px]">
                                {{ resultAvailableDomain.domainName }}

                                <!-- label doamin status -->
                                <div v-if="checkComboDomain() === '0d'"
                                    class="vnx_tablet:hidden relative mt-4 text-[12px] italic h-[30px] pl-4 text-white whitespace-nowrap pr-2 bg-gradient-to-l from-[#F2000B] to-[#AB0000] rounded">
                                    <span>
                                        Miễn phí tên miền khi mua combo
                                    </span>
                                    <img class="absolute w-[18px] top-[-2px] left-[-10px]"
                                        src="https://vietnix.vn/wp-content/uploads/2025/12/image-1.png" alt="">
                                </div>
                            </span>

                            <div v-if="resultAvailableDomain && resultAvailableDomain.pricing"
                                class="w-full items-center vnx_tablet:items-end flex justify-end vnx_tablet:justify-between ">

                                <div class="flex gap-2 leading-[30px] vnx_tablet:leading-[24px]">
                                    <!-- Giá gốc -->
                                    <span
                                        v-if="Number(getPriceOriginal(resultAvailableDomain.tld)) > 0
                                        && resultAvailableDomain.pricing
                                        && resultAvailableDomain.pricing.register
                                        && resultAvailableDomain.pricing.register['1']
                                        && Number(getPriceOriginal(resultAvailableDomain.tld)) > Number(resultAvailableDomain.pricing.register['1'])"
                                        class="text-[18px] line-through leading-6">
                                        {{ formatVND(getPriceOriginal(resultAvailableDomain.tld)) }}đ
                                    </span>

                                    <!-- Giá đăng ký  -->
                                    <span class="text-[20px]  vnx_tablet:text-[18px] font-bold whitespace-nowrap">
                                        {{formatVND(resultAvailableDomain.pricing &&
                                        resultAvailableDomain.pricing.register['1'])}}đ/Năm đầu
                                    </span>
                                </div>

                                <button @click="orderProduct"
                                    class="vnx_tablet:hidden bg-[linear-gradient(104deg,#FF8600_0%,#FFB300_100%)] leading-6 flex-none rounded-lg w-[128px] h-10 text-[#FCFCFC] text-[16px] font-medium ml-3">
                                    Mua ngay <i class="fa-regular fa-cart-shopping ml-2"></i>
                                </button>

                                <button @click="orderProduct"
                                    class="hidden vnx_tablet:block bg-[#007CFC] flex-none size-10 rounded-full text-white text-[16px] font-medium ml-3">
                                    <i class="fa-regular fa-cart-plus block"></i>
                                </button>
                            </div>

                            <!-- Xử lý tên miền đặc biệt -->
                            <div v-else
                                class="w-full text-[16px] mt-6 leading-6 font-medium items-center flex gap-12 justify-end vnx_tablet:justify-between">
                                <span
                                    class="text-[#FF6F00] px-4 rounded-full border border-[#FF6F00] text-[14px] h-8 flex center bg-[#FFF8D6]">
                                    Tên miền đặc biệt
                                </span>

                                <button
                                    class="btn_tawk text-[#007CFC] px-4 vnx_tablet:px-0 vnx_tablet:size-[32px] rounded-full border border-[#007CFC] text-[14px] h-8 flex center">
                                    <span class="vnx_tablet:hidden">Liên hệ </span><i
                                        class="ml-2 vnx_tablet:ml-0 fa-regular fa-comments"></i>
                                </button>
                            </div>

                            <!-- label doamin status -->
                            <div v-if="checkComboDomain() === '0d'" class="vnx_tablet:block hidden relative mt-3 ml-2 text-[12px] content-center
                            italic h-[30px] pl-4 text-white whitespace-nowrap pr-2 
                            bg-gradient-to-l from-[#F2000B] to-[#AB0000] rounded vnx_tablet:leading-[22px]">
                                <span>
                                    Miễn phí tên miền khi mua combo
                                </span>
                                <img class="absolute w-[18px] top-[-2px] left-[-10px]"
                                    src="https://vietnix.vn/wp-content/uploads/2025/12/image-1.png" alt="">
                            </div>



                        </div>
                    </div>
                </div>
            </div>


            <!-- Dịch vụ mua kèm  -->
            <div class="relative ">
                <!-- header  -->
                <p v-if="checkComboDomain() != ''"
                    class="text-[28px] vnx_tablet:text-[20px] vnx_tablet:leading-8 vnx_tablet:pt-6 vnx_tablet:py-0 py-2 px-6 vnx_tablet:px-4 leading-[40px] font-bold mb-10 vnx_tablet:mb-8 text-center text-[#282829]">
                    MUA COMBO SIÊU TIẾT KIỆM</p>

                <!-- Danh sách tên miền 0đ -->
                <div v-if="checkComboDomain() === '0d'"
                    class="domain-0d mt-10 flex gap-6 whitespace-nowrap overflow-hidden relative  vnx-sidebar-xscroll">
                    <div v-for="(service, index) in extraServices" :key="service.id" :style="{
                        'background-image': service.background?.url ? 'url(' + service.background.url + ')' : '',
                        'border-color': service.borderCard?.rgb || '#000'
                        }"
                        class="item-combo flex flex-col items-center gap-3 min-w-[364px] card-item border-[4px] rounded-2xl py-4 px-5  relative pt-[60px] w-full ">
                        <div class="flex flex-col w-full justify-between">

                            <!-- Label  -->

                            <div class="absolute right-5 top-6" v-if="getRowDataPriceCSV( index)[3]?.trim()">
                                <img class="h-6" :src="getRowDataPriceCSV(index)[3]" alt="">
                            </div>


                            <div class="mb-6">
                                <p class="font-bold text-[20px] vnx_tablet:text-[18px] text-[#282829] leading-[30px]">{{
                                    service.title }}</p>

                                <p class="text-[16px] mb-5  text-[#007CFC] leading-6 italic serviceName">{{ service.name
                                    }}</p>

                                <div
                                    class="relative center text-[16px] h-[32px] pl-4 text-white whitespace-nowrap pr-2 bg-gradient-to-l from-[#F2000B] to-[#AB0000] rounded">
                                    <span class="font-bold">
                                        {{ resultAvailableDomain.domainName }}
                                    </span>
                                    <span class="ml-[10px] italic">
                                        {{ getRowDataPriceCSV( index)[0] }}
                                    </span>
                                    <img class="absolute w-[18px] top-[-2px] left-[-10px]"
                                        src="https://vietnix.vn/wp-content/uploads/2025/12/image-1.png" alt="">
                                </div>


                            </div>

                            <div class="flex items-center mb-5 text-[#282829]">
                                <!-- Giá gốc  -->
                                <span
                                    v-if="getRowDataPriceCSV( index)[1] && getRowDataPriceCSV( index)[1].trim() !== ''"
                                    class="text-[16px] leading-8 relative">
                                    {{ getRowDataPriceCSV( index)[1] }}

                                    <svg class="absolute bottom-[5px]" xmlns="http://www.w3.org/2000/svg" width="100%"
                                        viewBox="0 0 75 13" fill="none">
                                        <path d="M0.0800781 12.4932L74.0801 0.493164" stroke="#282829" />
                                    </svg>
                                </span>
                                <span class="text-[28px] leading-10  font-extrabold ml-2 whitespace-nowrap ">
                                    {{ getRowDataPriceCSV( index)[2] }} <span
                                        class="text-[16px] font-normal leading-8"><?php echo esc_html($settings['unit']); ?></span>
                                </span>

                            </div>

                            <button
                                class="mb-6 h-[54px] rounded-[12px] shadow-[0_4px_20px_rgba(0,124,252,0.4)] bg-[linear-gradient(90deg,#007CFC_3.25%,#48CDFF_100%)] hover:bg-[linear-gradient(104deg,#007CFC_0%,#0691FF_100%)]">
                                <span class="text-[18px] leading-[30px] text-[#FCFCFC] font-medium">
                                    Đăng ký ngay
                                </span>
                            </button>

                            <div class="flex items-center gap-1 mb-2 text-[#424242] text-[14px] leading-6">
                                <span>
                                    Tốc độ:
                                </span>

                                <img v-if="service.imageSpeed?.url" class="w-[120px] h-4" :src="service.imageSpeed.url"
                                    alt="">
                            </div>

                            <!-- Thông tin CPU  -->
                            <div class="flex items-center">
                                <div class="flex items-center pr-2 border-r-2 border-[#C0C0C2] w-fit">
                                    <i class="fa-regular fa-microchip mr-2"></i>
                                    <span
                                        class="text-[14px] font-bold left-6 whitespace-nowrap leading-6 text-[#424242]">
                                        {{ service?.descriptions.split(',')[0] ?? '' }}
                                    </span>
                                </div>
                                <div class="relative active-tooltip cursor-pointer">
                                    <img v-if="service.imageSpecial?.url" class="h-6 pl-2"
                                        :src="service.imageSpecial.url" alt="">

                                    <!-- Tooltip -->
                                    <div
                                        class="hidden  tooltip absolute right-0 bg-[#007CFC] w-[207px] whitespace-normal rounded p-1 text-white text-[14px] top-[35px] leading-6">
                                        {{ service.tooltip }}
                                    </div>
                                </div>
                            </div>

                            <!-- đường line  -->
                            <div
                                class="h-[1px] w-full bg-gradient-to-r from-transparent via-black/30 to-transparent my-6">
                            </div>


                            <!-- thông tin dich vụ  -->
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-[14px] leading-6 text-[#282829]">
                                <div class="flex items-center ">
                                    <i class="fa-regular fa-microchip mr-2"></i>
                                    <span class="whitespace-nowrap">
                                        {{ service?.descriptions.split(',')[1] ?? '' }}
                                    </span>
                                </div>
                                <div class="flex items-center ">
                                    <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M11.1127 5.79924L12.1035 4.80839L10.5837 3.28857L9.59284 4.27942L11.1127 5.79924ZM8.98313 7.92876L9.97398 6.93791L8.45416 5.4181L7.46332 6.40894L8.98313 7.92876ZM6.93791 9.97398L7.92876 8.98313L6.40894 7.46332L5.4181 8.45416L6.93791 9.97398ZM4.80839 12.1035L5.79924 11.1127L4.27942 9.59284L3.28857 10.5837L4.80839 12.1035ZM11.7883 2.74833C11.5665 2.52654 11.5201 2.19393 11.6553 1.92846L11.1916 1.46479L1.46479 11.1916L1.92846 11.6553C2.19393 11.5201 2.52654 11.5665 2.74833 11.7883C2.96997 12.01 3.01577 12.3421 2.88072 12.6075L4.80839 14.5352L5.11235 14.2312L4.65641 13.7753L4.96037 13.4713L5.41631 13.9273L5.66328 13.6803L5.20734 13.2244L5.5113 12.9204L5.96725 13.3763L6.21422 13.1294L5.75827 12.6734L6.06224 12.3695L6.51818 12.8254L6.76515 12.5784L6.30921 12.1225L6.61317 11.8185L7.06911 12.2745L7.31608 12.0275L6.86014 11.5716L7.1641 11.2676L7.62005 11.7235L7.86702 11.4766L7.41107 11.0206L7.71503 10.7167L8.17098 11.1726L8.41795 10.9256L7.962 10.4697L8.26597 10.1657L8.72191 10.6217L8.96888 10.3747L8.51294 9.91877L8.8169 9.6148L9.27285 10.0707L9.51982 9.82378L9.06387 9.36783L9.36783 9.06387L9.82378 9.51982L10.0707 9.27285L9.6148 8.8169L9.91877 8.51294L10.3747 8.96888L10.6217 8.72191L10.1657 8.26597L10.4697 7.962L10.9256 8.41795L11.1726 8.17098L10.7167 7.71503L11.0206 7.41107L11.4766 7.86702L11.7235 7.62005L11.2676 7.1641L11.5716 6.86014L12.0275 7.31608L12.2745 7.06911L11.8185 6.61317L12.1225 6.30921L12.5784 6.76515L12.8254 6.51818L12.3695 6.06224L12.6734 5.75827L13.1294 6.21422L13.3763 5.96725L12.9204 5.5113L13.2244 5.20734L13.6803 5.66328L13.9273 5.41631L13.4713 4.96037L13.7753 4.65641L14.2312 5.11235L14.5352 4.80839L12.6075 2.88072C12.3421 3.01577 12.01 2.96997 11.7883 2.74833ZM6.18899 10.8945C6.3422 11.0478 6.26399 11.2558 6.17029 11.3495L5.04527 12.4745C4.95156 12.5682 4.74358 12.6465 4.59021 12.4932L2.89882 10.8019C2.74561 10.6485 2.82382 10.4405 2.91752 10.3468L4.04254 9.22179C4.13625 9.12809 4.34423 9.04988 4.4976 9.20309L6.18899 10.8945ZM8.31851 8.76495C8.47168 8.91828 8.39347 9.12626 8.29981 9.22001L7.17479 10.345C7.08109 10.4387 6.87311 10.5169 6.71973 10.3637L5.02835 8.67234C4.87513 8.51897 4.95334 8.31099 5.04705 8.21729L6.17207 7.09227C6.26581 6.9986 6.4738 6.9204 6.62712 7.07357L8.31851 8.76495ZM10.3637 6.71973C10.5169 6.87311 10.4387 7.08108 10.345 7.17479L9.22001 8.29981C9.12626 8.39347 8.91828 8.47168 8.76495 8.31851L7.07357 6.62712C6.9204 6.4738 6.9986 6.26581 7.09227 6.17207L8.21729 5.04705C8.31099 4.95334 8.51897 4.87513 8.67234 5.02835L10.3637 6.71973ZM12.4932 4.59021C12.6465 4.74358 12.5682 4.95156 12.4745 5.04527L11.3495 6.17029C11.2558 6.26399 11.0478 6.3422 10.8945 6.18899L9.20309 4.4976C9.04988 4.34423 9.12809 4.13625 9.22179 4.04254L10.3468 2.91752C10.4405 2.82382 10.6485 2.74561 10.8019 2.89882L12.4932 4.59021ZM11.994 1.65922C12.1346 1.79983 12.1101 1.99561 12.0435 2.11309C11.9855 2.21564 12.0004 2.35251 12.0922 2.44436C12.1841 2.5361 12.3207 2.55077 12.4232 2.49275C12.5407 2.42616 12.7365 2.40172 12.8771 2.54232L14.8982 4.5635C15.0314 4.69669 15.036 4.91552 14.8994 5.05209L5.05209 14.8994C4.91552 15.036 4.69669 15.0314 4.5635 14.8982L2.54232 12.8771C2.40172 12.7365 2.42616 12.5407 2.49275 12.4232L2.51115 12.3834C2.54604 12.2873 2.52466 12.1726 2.44436 12.0922C2.35251 12.0004 2.21564 11.9855 2.11309 12.0435C2.00294 12.106 1.82402 12.1316 1.68624 12.0186L1.65922 11.994L1.10176 11.4365C0.968586 11.3033 0.964 11.0845 1.10057 10.9479L10.9479 1.10057C11.0845 0.963999 11.3033 0.968587 11.4365 1.10176L11.994 1.65922Z"
                                            fill="black" />
                                    </svg>
                                    <span class="whitespace-nowrap">
                                        {{ service?.descriptions.split(',')[2] ?? '' }}
                                    </span>
                                </div>
                                <div class="flex items-center ">
                                    <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M14 4H2C1.73478 4 1.48043 4.10536 1.29289 4.29289C1.10536 4.48043 1 4.73478 1 5V11C1 11.2652 1.10536 11.5196 1.29289 11.7071C1.48043 11.8946 1.73478 12 2 12H14C14.2652 12 14.5196 11.8946 14.7071 11.7071C14.8946 11.5196 15 11.2652 15 11V5C15 4.73478 14.8946 4.48043 14.7071 4.29289C14.5196 4.10536 14.2652 4 14 4ZM14 11H2V5H14V11ZM12.5 8C12.5 8.14834 12.456 8.29334 12.3736 8.41668C12.2912 8.54001 12.1741 8.63614 12.037 8.69291C11.9 8.74967 11.7492 8.76453 11.6037 8.73559C11.4582 8.70665 11.3246 8.63522 11.2197 8.53033C11.1148 8.42544 11.0434 8.2918 11.0144 8.14632C10.9855 8.00083 11.0003 7.85003 11.0571 7.71299C11.1139 7.57594 11.21 7.45881 11.3333 7.3764C11.4567 7.29399 11.6017 7.25 11.75 7.25C11.9489 7.25 12.1397 7.32902 12.2803 7.46967C12.421 7.61032 12.5 7.80109 12.5 8Z"
                                            fill="#282829" />
                                    </svg>
                                    <span class="whitespace-nowrap">
                                        {{ service?.descriptions.split(',')[3] ?? '' }}
                                    </span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fa-regular fa-globe mr-2"></i>
                                    <span class="whitespace-nowrap">
                                        {{ service?.descriptions.split(',')[4] ?? '' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end Danh sách tên miền 0đ-->

                <!-- Danh sách tên miền combo-->
                <div v-if="checkComboDomain() === 'combo'"
                    class="domain-combo mt-10 flex gap-6 whitespace-nowrap overflow-hidden relative  vnx-sidebar-xscroll2">
                    <div v-for="(service, index) in domainCombos" :key="service.id" :style="{
                        'background-image': service.background?.url ? 'url(' + service.background.url + ')' : '',
                        'border-color': service.borderCard?.rgb || '#000'
                        }"
                        class="item-combo flex flex-col items-center gap-3 min-w-[364px] card-item border-[4px] rounded-2xl py-4 px-5  relative pt-[60px] w-full ">
                        <div class="flex flex-col w-full justify-between">

                            <!-- Label  -->

                            <div class="absolute right-5 top-6" v-if="getRowDataPriceCSV( index)[3]?.trim()">
                                <img class="h-6" :src="getRowDataPriceCSV( index)[3]" alt="">
                            </div>


                            <div class="mb-8">
                                <p class="font-bold text-[20px] vnx_tablet:text-[18px] text-[#282829] leading-[30px]">{{
                                    service.title }}</p>

                                <p class="text-[16px] mb-5 font-bold  text-[#007CFC] leading-6 serviceName">
                                    {{ resultAvailableDomain.domainName }}{{ service.name }}
                                </p>

                                <div class="flex flex-col justify-center  mt-4 text-[16px] h-[88px] w-[324px] pl-4 text-white whitespace-nowrap rounded relative"
                                    :style="{
                                        'background-image': service.imageLabel && service.imageLabel.url ? 'url(' + service.imageLabel.url + ')' : 'none'
                                    }">

                                    <!-- Giá gốc  -->
                                    <span class="text-[16px] leading-8 relative w-fit">
                                        {{ getRowDataPriceCSV( index)[0] }}
                                        <svg class="absolute bottom-[5px]" xmlns="http://www.w3.org/2000/svg"
                                            width="100%" viewBox="0 0 75 13" fill="none">
                                            <path d="M0.0800781 12.4932L74.0801 0.493164" stroke="#FCFCFC" />
                                        </svg>
                                    </span>

                                    <!-- Giá bán  -->
                                    <span class="font-extrabold text-[28px] leading-10">
                                        {{ getRowDataPriceCSV( index)[1] }}<span
                                            class="text-[16px] font-normal leading-8"><?php echo esc_html($settings['unit']); ?></span>
                                    </span>

                                    <!-- Khuyến mãi  -->
                                    <div class="absolute text-[20px] text-[#DE020A] leading-[30px] font-bold right-2">
                                        {{ getRowDataPriceCSV( index)[2] }}
                                    </div>
                                </div>


                            </div>



                            <button
                                class="mb-6 h-[54px] rounded-[12px] shadow-[0_4px_20px_rgba(0,124,252,0.4)] bg-[linear-gradient(90deg,#007CFC_3.25%,#48CDFF_100%)] hover:bg-[linear-gradient(104deg,#007CFC_0%,#0691FF_100%)]">
                                <span class="text-[18px] leading-[30px] text-[#FCFCFC] font-medium">
                                    Đăng ký ngay
                                </span>
                            </button>

                            <div class="flex items-center gap-1 mb-2 text-[#424242] text-[14px] leading-6">
                                <span>
                                    Tốc độ:
                                </span>

                                <img v-if="service.imageSpeed?.url" class="w-[120px] h-4" :src="service.imageSpeed.url"
                                    alt="">
                            </div>

                            <!-- Thông tin CPU  -->
                            <div class="flex items-center">
                                <div class="flex items-center pr-2 border-r-2 border-[#C0C0C2] w-fit">
                                    <i class="fa-regular fa-microchip mr-2"></i>
                                    <span
                                        class="text-[14px] font-bold left-6 whitespace-nowrap leading-6 text-[#424242]">
                                        {{ service?.descriptions.split(',')[0] ?? '' }}
                                    </span>
                                </div>
                                <div class="relative active-tooltip cursor-pointer">
                                    <img v-if="service.imageSpecial?.url" class="h-6 pl-2"
                                        :src="service.imageSpecial.url" alt="">

                                    <!-- Tooltip -->
                                    <div
                                        class="hidden  tooltip absolute right-0 bg-[#007CFC] w-[207px] whitespace-normal rounded p-1 text-white text-[14px] top-[35px] leading-6">
                                        {{ service.tooltip }}
                                    </div>
                                </div>
                            </div>

                            <!-- đường line  -->
                            <div
                                class="h-[1px] w-full bg-gradient-to-r from-transparent via-black/30 to-transparent my-6">
                            </div>


                            <!-- thông tin dich vụ  -->
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-[14px] leading-6 text-[#282829]">
                                <div class="flex items-center ">
                                    <i class="fa-regular fa-microchip mr-2"></i>
                                    <span class="whitespace-nowrap">
                                        {{ service?.descriptions.split(',')[1] ?? '' }}
                                    </span>
                                </div>
                                <div class="flex items-center ">
                                    <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M11.1127 5.79924L12.1035 4.80839L10.5837 3.28857L9.59284 4.27942L11.1127 5.79924ZM8.98313 7.92876L9.97398 6.93791L8.45416 5.4181L7.46332 6.40894L8.98313 7.92876ZM6.93791 9.97398L7.92876 8.98313L6.40894 7.46332L5.4181 8.45416L6.93791 9.97398ZM4.80839 12.1035L5.79924 11.1127L4.27942 9.59284L3.28857 10.5837L4.80839 12.1035ZM11.7883 2.74833C11.5665 2.52654 11.5201 2.19393 11.6553 1.92846L11.1916 1.46479L1.46479 11.1916L1.92846 11.6553C2.19393 11.5201 2.52654 11.5665 2.74833 11.7883C2.96997 12.01 3.01577 12.3421 2.88072 12.6075L4.80839 14.5352L5.11235 14.2312L4.65641 13.7753L4.96037 13.4713L5.41631 13.9273L5.66328 13.6803L5.20734 13.2244L5.5113 12.9204L5.96725 13.3763L6.21422 13.1294L5.75827 12.6734L6.06224 12.3695L6.51818 12.8254L6.76515 12.5784L6.30921 12.1225L6.61317 11.8185L7.06911 12.2745L7.31608 12.0275L6.86014 11.5716L7.1641 11.2676L7.62005 11.7235L7.86702 11.4766L7.41107 11.0206L7.71503 10.7167L8.17098 11.1726L8.41795 10.9256L7.962 10.4697L8.26597 10.1657L8.72191 10.6217L8.96888 10.3747L8.51294 9.91877L8.8169 9.6148L9.27285 10.0707L9.51982 9.82378L9.06387 9.36783L9.36783 9.06387L9.82378 9.51982L10.0707 9.27285L9.6148 8.8169L9.91877 8.51294L10.3747 8.96888L10.6217 8.72191L10.1657 8.26597L10.4697 7.962L10.9256 8.41795L11.1726 8.17098L10.7167 7.71503L11.0206 7.41107L11.4766 7.86702L11.7235 7.62005L11.2676 7.1641L11.5716 6.86014L12.0275 7.31608L12.2745 7.06911L11.8185 6.61317L12.1225 6.30921L12.5784 6.76515L12.8254 6.51818L12.3695 6.06224L12.6734 5.75827L13.1294 6.21422L13.3763 5.96725L12.9204 5.5113L13.2244 5.20734L13.6803 5.66328L13.9273 5.41631L13.4713 4.96037L13.7753 4.65641L14.2312 5.11235L14.5352 4.80839L12.6075 2.88072C12.3421 3.01577 12.01 2.96997 11.7883 2.74833ZM6.18899 10.8945C6.3422 11.0478 6.26399 11.2558 6.17029 11.3495L5.04527 12.4745C4.95156 12.5682 4.74358 12.6465 4.59021 12.4932L2.89882 10.8019C2.74561 10.6485 2.82382 10.4405 2.91752 10.3468L4.04254 9.22179C4.13625 9.12809 4.34423 9.04988 4.4976 9.20309L6.18899 10.8945ZM8.31851 8.76495C8.47168 8.91828 8.39347 9.12626 8.29981 9.22001L7.17479 10.345C7.08109 10.4387 6.87311 10.5169 6.71973 10.3637L5.02835 8.67234C4.87513 8.51897 4.95334 8.31099 5.04705 8.21729L6.17207 7.09227C6.26581 6.9986 6.4738 6.9204 6.62712 7.07357L8.31851 8.76495ZM10.3637 6.71973C10.5169 6.87311 10.4387 7.08108 10.345 7.17479L9.22001 8.29981C9.12626 8.39347 8.91828 8.47168 8.76495 8.31851L7.07357 6.62712C6.9204 6.4738 6.9986 6.26581 7.09227 6.17207L8.21729 5.04705C8.31099 4.95334 8.51897 4.87513 8.67234 5.02835L10.3637 6.71973ZM12.4932 4.59021C12.6465 4.74358 12.5682 4.95156 12.4745 5.04527L11.3495 6.17029C11.2558 6.26399 11.0478 6.3422 10.8945 6.18899L9.20309 4.4976C9.04988 4.34423 9.12809 4.13625 9.22179 4.04254L10.3468 2.91752C10.4405 2.82382 10.6485 2.74561 10.8019 2.89882L12.4932 4.59021ZM11.994 1.65922C12.1346 1.79983 12.1101 1.99561 12.0435 2.11309C11.9855 2.21564 12.0004 2.35251 12.0922 2.44436C12.1841 2.5361 12.3207 2.55077 12.4232 2.49275C12.5407 2.42616 12.7365 2.40172 12.8771 2.54232L14.8982 4.5635C15.0314 4.69669 15.036 4.91552 14.8994 5.05209L5.05209 14.8994C4.91552 15.036 4.69669 15.0314 4.5635 14.8982L2.54232 12.8771C2.40172 12.7365 2.42616 12.5407 2.49275 12.4232L2.51115 12.3834C2.54604 12.2873 2.52466 12.1726 2.44436 12.0922C2.35251 12.0004 2.21564 11.9855 2.11309 12.0435C2.00294 12.106 1.82402 12.1316 1.68624 12.0186L1.65922 11.994L1.10176 11.4365C0.968586 11.3033 0.964 11.0845 1.10057 10.9479L10.9479 1.10057C11.0845 0.963999 11.3033 0.968587 11.4365 1.10176L11.994 1.65922Z"
                                            fill="black" />
                                    </svg>
                                    <span class="whitespace-nowrap">
                                        {{ service?.descriptions.split(',')[2] ?? '' }}
                                    </span>
                                </div>
                                <div class="flex items-center ">
                                    <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M14 4H2C1.73478 4 1.48043 4.10536 1.29289 4.29289C1.10536 4.48043 1 4.73478 1 5V11C1 11.2652 1.10536 11.5196 1.29289 11.7071C1.48043 11.8946 1.73478 12 2 12H14C14.2652 12 14.5196 11.8946 14.7071 11.7071C14.8946 11.5196 15 11.2652 15 11V5C15 4.73478 14.8946 4.48043 14.7071 4.29289C14.5196 4.10536 14.2652 4 14 4ZM14 11H2V5H14V11ZM12.5 8C12.5 8.14834 12.456 8.29334 12.3736 8.41668C12.2912 8.54001 12.1741 8.63614 12.037 8.69291C11.9 8.74967 11.7492 8.76453 11.6037 8.73559C11.4582 8.70665 11.3246 8.63522 11.2197 8.53033C11.1148 8.42544 11.0434 8.2918 11.0144 8.14632C10.9855 8.00083 11.0003 7.85003 11.0571 7.71299C11.1139 7.57594 11.21 7.45881 11.3333 7.3764C11.4567 7.29399 11.6017 7.25 11.75 7.25C11.9489 7.25 12.1397 7.32902 12.2803 7.46967C12.421 7.61032 12.5 7.80109 12.5 8Z"
                                            fill="#282829" />
                                    </svg>
                                    <span class="whitespace-nowrap">
                                        {{ service?.descriptions.split(',')[3] ?? '' }}
                                    </span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fa-regular fa-globe mr-2"></i>
                                    <span class="whitespace-nowrap">
                                        {{ service?.descriptions.split(',')[4] ?? '' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end Danh sách  tên miền combo-->


                <div v-if="checkComboDomain()" class="w-full flex center mt-10 mb-12 vnx_tablet:mb-6 vnx_tablet:mt-0">
                    <a href="/web-hosting"
                        class="text-[#007CFC] text-[18px] font-medium leading-[30px] py-3 vnx_tablet:text-[14px] vnx_tablet:leading-6">
                        XEM CÁC GÓI HOSTING KHÁC
                        <i class="ml-2 fa-regular fa-arrow-up"
                            style="display:inline-block; transform: rotate(45deg); transform-origin: center;"></i>
                    </a>
                </div>

            </div>
        </div>
        <!--end Hiển thị kết quả Đăng ký domain -->
    </div>
</div>