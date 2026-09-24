<?php
if (!defined('ABSPATH'))
    exit;
$data = isset($data) ? $data : new stdClass();
$data->set_attribute('_root', 'class', 'vnx-cycle-component-whois-domain');
$settings = $data->settings;
?>

<div v-cloak <?php echo $data->render_attributes('_root'); ?>>
    <div
        v-show="resultWhoisDomain && resultAvailableDomain && !resultAvailableDomain.isAvailable && resultWhoisDomain?.domainStatus != 'undefined' && resultWhoisDomain?.domainStatus != 'reserved'"
        class="cycle-component-wrapper relative">

        <!-- loading  -->
        <div v-if="isLoading" class="absolute w-full h-full flex justify-center items-center opacity-40 bg-white z-10">
            <span v-if="true" class="flex"><svg class="animate-[spin_0.4s_linear_infinite] size-9 mr-2 text-[#008cff] z-11"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </span>
        </div>


        <div class="cycle-component-title">
            <h2>Vòng đời tên miền <span>{{resultAvailableDomain?.domainName ?? ''}}</span></h2>
            <p>Vòng đời mang tính chất tham khảo. Các mốc thời gian sau ngày hiện tại là mốc thời gian dự kiến nếu tên
                miền không có thay đổi nào. Xem thông tin cách tính và quy định của vòng đời tên miền <a href="https://vietnix.vn/vong-doi-ten-mien/">tại
                    đây.</a></p>
        </div>

        <!-- Vòng đời tiên miền VN -->

        <div class="vnx-sidebar-xscroll relative">
            <div class="domain-timeline" v-if="resultWhoisDomain?.domain_type === 'vn'">
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date">{{formatDate(resultWhoisDomain?.creationDate)}}</span>
                        <span class="dt-title">Ngày đăng ký tên miền</span>
                        <span v-if="resultWhoisDomain?.Registrar" class="dt-description">Tên miền được đăng ký bởi Nhà đăng ký
                            <strong>{{resultWhoisDomain?.Registrar}}</strong></span>
                    </div>

                    <span class="divider"></span>
                </div>
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date">{{formatDate(new Date())}}</span>
                        <span class="dt-title">Tên miền đang hoạt động</span>
                        <span class="dt-description">Tên miền được {{calculateAge(resultWhoisDomain?.creationDate)}} tuổi</span>
                    </div>
                    <span class="label-day">{{dateDiffInDays(resultWhoisDomain?.creationDate, new Date())}} ngày</span>
                    <span class="divider"></span>

                </div>
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date"> {{formatDate(resultWhoisDomain?.expirationDate)}}</span>
                        <span class="dt-title">Cảnh báo: Tên miền đã tạm ngưng</span>
                        <span class="dt-description">Ngay sau khi hết hạn, website sẽ ngừng hoạt động. Bạn có 30 ngày để gia hạn.</span>
                    </div>
                    <span class="label-day">{{dateDiffInDays(new Date(), resultWhoisDomain?.expirationDate)}} ngày</span>
                    <span class="divider"></span>
                </div>
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date">{{getDay(resultWhoisDomain?.expirationDate, 30, false)}}</span>
                        <span class="dt-title">Tên miền chờ thu hồi</span>
                        <span class="dt-description">Tên miền bị khóa, bạn không thể gia hạn.</span>
                    </div>
                    <span class="label-day">30 ngày</span>
                    <span class="divider"></span>
                </div>
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date">{{getDay(getDay(resultWhoisDomain?.expirationDate, 30, false), 15, false)}}</span>
                        <span class="dt-title">Tự do & Đăng ký trở lại</span>
                        <span class="dt-description">Tên miền ở trạng thái tự do, mọi người đều có thể đăng ký lại.</span>
                    </div>
                    <span class="label-day">15 ngày</span>
                    <span class="divider"></span>
                </div>

            </div>

            <!-- Vòng đời tiên miền quốc tế  -->

            <div class="domain-timeline" v-if="resultWhoisDomain?.domain_type === 'qt'">
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date">{{formatDate(resultWhoisDomain?.creationDate)}}</span>
                        <span class="dt-title">Ngày đăng ký tên miền</span>
                        <span v-if="resultWhoisDomain?.Registrar" class="dt-description">Tên miền được đăng ký bởi Nhà đăng ký
                            <strong>{{resultWhoisDomain?.Registrar}}</strong></span>
                    </div>

                    <span class="divider"></span>
                </div>
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date">{{formatDate(new Date())}}</span>
                        <span class="dt-title">Tên miền đang hoạt động</span>
                        <span class="dt-description">Tên miền được {{calculateAge(resultWhoisDomain?.creationDate)}} tuổi</span>
                    </div>
                    <span class="label-day">{{dateDiffInDays(resultWhoisDomain?.creationDate, new Date())}} ngày</span>
                    <span class="divider"></span>

                </div>
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date"> {{formatDate(resultWhoisDomain?.expirationDate)}}</span>
                        <span class="dt-title">Tên miền đã hết hạn</span>
                        <span class="dt-description">Gia hạn ngay chỉ với chi phí gốc để tránh gián đoạn dịch vụ.</span>
                    </div>
                    <span class="label-day">{{dateDiffInDays(new Date(), resultWhoisDomain?.expirationDate)}} ngày</span>
                    <span class="divider"></span>
                </div>
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date">{{getDay(resultWhoisDomain?.expirationDate, 30, false)}}</span>
                        <span class="dt-title">Cảnh báo: Tên miền bị tạm ngưng</span>
                        <span class="dt-description">Dịch vụ bị gián đoạn. Cần trả phí chuộc cao gấp nhiều lần để khôi phục.</span>
                    </div>
                    <span class="label-day">30 ngày</span>
                    <span class="divider"></span>
                </div>
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date">{{getDay(getDay(resultWhoisDomain?.expirationDate, 30, false), 35, false)}}</span>
                        <span class="dt-title">Chờ xóa vĩnh viễn tên miền</span>
                        <span class="dt-description">Tên miền bị khóa hoàn toàn và đang trong hàng đợi xóa. Bạn đã mất mọi quyền kiểm soát.</span>
                    </div>
                    <span class="label-day">35 ngày</span>
                    <span class="divider"></span>
                </div>
                <div class="domain-timeline-item">
                    <div class="domain-timeline-item-box">
                        <span class="dt-date">{{getDay(getDay(getDay(resultWhoisDomain?.expirationDate, 30, false), 35, false), 5, false)}}</span>
                        <span class="dt-title">Tự do & Đăng ký lại</span>
                        <span class="dt-description">Tên miền đã tự do. Bất kì ai đều có thể đăng ký ngay.</span>
                    </div>
                    <span class="label-day">5 ngày</span>
                    <span class="divider"></span>
                </div>
            </div>
        </div>

        <div
            v-if="resultWhoisDomain && resultAvailableDomain && !resultAvailableDomain.isAvailable"
            class="cycle-component-divider"><svg xmlns="http://www.w3.org/2000/svg" width="800" height="2" viewBox="0 0 800 2" fill="none">
                <path d="M0 1H800" stroke="url(#paint0_linear_4695_10284)" />
                <defs>
                    <linearGradient id="paint0_linear_4695_10284" x1="0" y1="1.5" x2="800" y2="1.5"
                        gradientUnits="userSpaceOnUse">
                        <stop stop-color="#C0C0C2" stop-opacity="0" />
                        <stop offset="0.5" stop-color="#C0C0C2" />
                        <stop offset="1" stop-color="#C0C0C2" stop-opacity="0" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
    </div>
</div>