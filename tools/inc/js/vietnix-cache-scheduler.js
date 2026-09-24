jQuery(function ($) {
  const WEEKDAY_OPTIONS = [
    { value: 1, label: "T2" },
    { value: 2, label: "T3" },
    { value: 3, label: "T4" },
    { value: 4, label: "T5" },
    { value: 5, label: "T6" },
    { value: 6, label: "T7" },
    { value: 7, label: "CN" },
  ];

  const defaultForm = () => ({
    id: "",
    label: "",
    purge_type: "all",
    urls: "",
    schedule_type: "once",
    run_at: "",
    time_of_day: "00:00",
    weekdays: [],
    interval_minutes: 60,
    enabled: false,
  });

  // Vue 2 khong thay el van tu tao div tam va chay mounted() (goi AJAX), nen chi khoi tao khi co giao dien cua tool tren trang.
  if (!document.querySelector("#vietnix-cache-scheduler")) return;

  new Vue({
    el: "#vietnix-cache-scheduler",
    data: {
      jobs: [],
      lsStatus: "active",
      autoEnabled: false,
      notify: { enabled: false, webhook_url: "" },
      showNotify: false,
      savingNotify: false,
      testingNotify: false,
      showPublicUrls: false,
      loadingPublicUrls: false,
      publicUrls: [],
      loading: false,
      saving: false,
      showForm: false,
      formError: "",
      form: defaultForm(),
      weekdayOptions: WEEKDAY_OPTIONS,
      alertMessage: "",
      alertType: "success",
      alertTimer: null,
      // id các lịch đang được poll, tránh mở nhiều vòng chồng nhau.
      pollingJobs: {},
      globalWatchTimer: null,
    },
    mounted() {
      this.fetchJobs();
      this.startGlobalWatch();
    },
    beforeDestroy() {
      window.clearInterval(this.globalWatchTimer);
    },
    methods: {
      request(action, data) {
        return jQuery.ajax({
          url: ajaxurl,
          type: "POST",
          data: Object.assign({ action, nonce: vnxCacheSchedulerData.nonce }, data || {}),
        });
      },

      // Vue 2 chỉ reactive các property có sẵn từ đầu, nên phải set default ở đây.
      normalizeJobs(jobs) {
        return (jobs || []).map((job) =>
          Object.assign(
            { running: false, cancelling: false, cancel_requested: false, cancel_force_in: null, run_alive: false, progress: null },
            job
          )
        );
      },

      fetchJobs() {
        this.loading = true;
        this.request("vnx_cache_scheduler_list")
          .done((response) => {
            if (response.success) {
              this.jobs = this.normalizeJobs(response.data.jobs);
              this.lsStatus = response.data.ls_status;
              this.autoEnabled = response.data.auto_enabled;
              this.notify = response.data.notify;

              this.jobs
                .filter((job) => job.last_status === "running")
                .forEach((job) => this.pollRunningJob(job.id));
            }
          })
          .fail(() => this.showAlert("Không tải được danh sách lịch", "error"))
          .always(() => (this.loading = false));
      },

      openPublicUrls() {
        this.showPublicUrls = true;
        this.loadingPublicUrls = true;
        this.request("vnx_cache_scheduler_public_urls")
          .done((response) => {
            if (response.success) {
              this.publicUrls = response.data.urls;
            } else {
              this.showAlert(response.data || "Không lấy được danh sách trang public", "error");
            }
          })
          .fail(() => this.showAlert("Lỗi kết nối", "error"))
          .always(() => (this.loadingPublicUrls = false));
      },

      openCreateForm() {
        this.form = defaultForm();
        this.formError = "";
        this.showForm = true;
      },

      openEditForm(job) {
        this.form = {
          id: job.id,
          label: job.label,
          purge_type: job.purge_type,
          urls: (job.urls || []).join("\n"),
          schedule_type: job.schedule_type,
          run_at: job.run_at ? job.run_at.replace(" ", "T") : "",
          time_of_day: job.time_of_day,
          weekdays: (job.weekdays || []).slice(),
          interval_minutes: job.interval_minutes,
          enabled: job.enabled,
        };
        this.formError = "";
        this.showForm = true;
      },

      closeForm() {
        this.showForm = false;
      },

      submitForm() {
        this.saving = true;
        this.formError = "";
        this.request("vnx_cache_scheduler_save", { job: this.form })
          .done((response) => {
            if (response.success) {
              this.jobs = this.normalizeJobs(response.data.jobs);
              this.showForm = false;
              this.showAlert("Đã lưu lịch hẹn", "success");
            } else {
              // Lỗi của form thì hiện trong modal, không phải banner đầu trang.
              this.formError = response.data || "Lưu lịch thất bại";
            }
          })
          .fail(() => (this.formError = "Lỗi kết nối"))
          .always(() => (this.saving = false));
      },

      toggleJob(job, inputEl) {
        this.request("vnx_cache_scheduler_toggle", { id: job.id })
          .done((response) => {
            if (response.success) {
              this.jobs = this.normalizeJobs(response.data.jobs);
              const updated = response.data.jobs.find((j) => j.id === job.id);
              this.showAlert(
                updated && updated.enabled ? 'Đã bật lịch "' + job.label + '"' : 'Đã tắt lịch "' + job.label + '"',
                "success"
              );
            } else {
              if (inputEl) inputEl.checked = job.enabled;
              this.showAlert(response.data || "Không thể bật lịch", "error");
            }
          })
          .fail(() => {
            if (inputEl) inputEl.checked = job.enabled;
            this.showAlert("Lỗi kết nối", "error");
          });
      },

      runNow(job) {
        job.running = true;
        this.request("vnx_cache_scheduler_run_now", { id: job.id })
          .done((response) => {
            if (!response.success) {
              this.showAlert(response.data || "Chạy lịch thất bại", "error");
              return;
            }

            this.jobs = this.normalizeJobs(response.data.jobs);

            if (response.data.queued) {
              this.showAlert('Đã bắt đầu tiến trình xoá cache cho lịch "' + job.label + '", cập nhật khi xong', "success");
              this.pollRunningJob(job.id);
            } else {
              this.showAlert('Đã xoá cache theo lịch "' + job.label + '"', "success");
            }
          })
          .fail(() => this.showAlert("Lỗi kết nối", "error"))
          .always(() => (job.running = false));
      },

      // Xin huỷ mà tiến trình vẫn chạy quá thời gian ân hạn thì mới cho dừng cứng.
      canForceCancel(job) {
        return !!job.cancel_requested && job.cancel_force_in === 0;
      },

      cancelButtonLabel(job) {
        if (this.canForceCancel(job)) return "Dừng khẩn cấp";
        if (job.cancelling || job.cancel_requested) return "Đang huỷ...";
        return "Huỷ";
      },

      cancelConfirmText(job) {
        if (this.canForceCancel(job)) {
          return (
            'Tiến trình của lịch "' +
            job.label +
            '" không dừng theo yêu cầu huỷ. Nhả khoá và kết thúc lượt chạy ngay? Tiến trình cũ sẽ tự thoát ở chốt kiểm tra kế tiếp.'
          );
        }

        return 'Huỷ lượt xoá cache đang chạy của lịch "' + job.label + '"?';
      },

      cancelJob(job, force) {
        job.cancelling = true;
        this.request("vnx_cache_scheduler_cancel", { id: job.id, force: force ? 1 : 0 })
          .done((response) => {
            if (!response.success) {
              this.showAlert(response.data || "Huỷ lượt chạy thất bại", "error");
              return;
            }

            this.jobs = this.normalizeJobs(response.data.jobs);

            if (response.data.lock_cleared) {
              this.showAlert("Lịch không còn chạy nhưng khoá vẫn kẹt, đã dọn khoá. Chạy lại được ngay.", "success");
              return;
            }

            if (response.data.forced) {
              this.showAlert('Đã dừng khẩn cấp lượt chạy của lịch "' + job.label + '" và nhả khoá', "success");
              return;
            }

            if (response.data.stopped) {
              this.showAlert('Lượt chạy của lịch "' + job.label + '" đã chết, đã dọn và mở khoá', "success");
              return;
            }

            const at = response.data.total ? " (đã crawl " + response.data.done + "/" + response.data.total + ")" : "";

            const waitMore =
              response.data.force_available_in > 0
                ? ". Nếu sau " + response.data.force_available_in + "s vẫn chưa dừng, nút sẽ đổi thành Dừng khẩn cấp"
                : "";

            this.showAlert(
              response.data.already
                ? 'Lịch "' + job.label + '" vẫn đang chạy' + at + ", sẽ dừng ngay khi cào xong URL hiện tại" + waitMore
                : 'Đã nhận yêu cầu huỷ lịch "' + job.label + '"' + at + ", tiến trình dừng sau khi cào xong URL hiện tại" + waitMore,
              "success"
            );

            this.pollRunningJob(job.id);
          })
          .fail(() => this.showAlert("Lỗi kết nối", "error"))
          .always(() => (job.cancelling = false));
      },

      // Job có thể tự chuyển sang "running" do WP-Cron chạy nền, không qua bất kỳ
      // thao tác nào của người dùng (runNow/cancelJob) - hai chỗ duy nhất tự bật
      // pollRunningJob(). Không có vòng này thì nút chỉ cập nhật sau khi F5.
      // fetchJobs() vẫn giữ nguyên cho lúc mount (có loading state riêng); ở đây
      // gọi thẳng list API, im lặng bỏ qua lỗi kết nối lẻ tẻ.
      startGlobalWatch() {
        const GLOBAL_WATCH_INTERVAL = 15000;
        this.globalWatchTimer = window.setInterval(() => {
          this.request("vnx_cache_scheduler_list")
            .done((response) => {
              if (!response.success) return;

              this.jobs = this.normalizeJobs(response.data.jobs);
              this.lsStatus = response.data.ls_status;
              this.autoEnabled = response.data.auto_enabled;
              this.notify = response.data.notify;

              this.jobs
                .filter((job) => job.last_status === "running" && !this.pollingJobs[job.id])
                .forEach((job) => this.pollRunningJob(job.id));
            });
        }, GLOBAL_WATCH_INTERVAL);
      },

      // Hỏi lại server mỗi 10s tới khi job hết running; trần đủ phủ RUN_STALE_AFTER (3600s) phía PHP.
      pollRunningJob(jobId, attempt) {
        const POLL_MAX_ATTEMPTS = 420;
        const current = attempt || 0;

        if (current === 0 && this.pollingJobs[jobId]) return;
        this.pollingJobs[jobId] = true;

        if (current >= POLL_MAX_ATTEMPTS) {
          delete this.pollingJobs[jobId];

          this.showAlert(
            "Đã ngừng theo dõi lịch này vì chờ quá lâu. Tải lại trang để xem trạng thái mới nhất.",
            "error"
          );
          return;
        }

        window.setTimeout(() => {
          this.request("vnx_cache_scheduler_list")
            .done((response) => {
              if (!response.success) {
                delete this.pollingJobs[jobId];
                return;
              }

              this.jobs = this.normalizeJobs(response.data.jobs);
              this.notify = response.data.notify;
              const job = this.jobs.find((item) => item.id === jobId);
              if (!job) {
                delete this.pollingJobs[jobId];
                return;
              }

              if (job.last_status === "running") {
                this.pollRunningJob(jobId, current + 1);
                return;
              }

              delete this.pollingJobs[jobId];

              const doneMessage = {
                success: 'Đã xoá cache xong lịch "' + job.label + '"',
                cancelled: 'Đã huỷ lượt chạy của lịch "' + job.label + '"',
              };

              this.showAlert(
                doneMessage[job.last_status] || 'Lịch "' + job.label + '" chạy thất bại',
                job.last_status === "error" ? "error" : "success"
              );
            })
            .fail(() => delete this.pollingJobs[jobId]);
        }, 10000);
      },

      isRunning(job) {
        return job.running || job.last_status === "running";
      },

      statusBadgeClass(status) {
        if (status === "cancelling" || status === "stalled") return "vnx-badge--amber";
        if (status === "success") return "vnx-badge--green";
        if (status === "running") return "vnx-badge--blue";
        if (status === "cancelled") return "vnx-badge--gray";
        return "vnx-badge--red";
      },

      // "stalled": job còn ở running nhưng tiến trình nền đã tắt nhịp tim.
      rowStatus(job) {
        if (job.last_status !== "running") return job.last_status;
        if (job.cancel_requested) return "cancelling";

        return job.run_alive ? "running" : "stalled";
      },

      statusLabel(status) {
        if (status === "cancelling") return "đang huỷ...";
        if (status === "stalled") return "mất tín hiệu";
        if (status === "success") return "thành công";
        if (status === "running") return "đang chạy";
        if (status === "cancelled") return "đã huỷ";
        return "lỗi";
      },

      toggleAuto(enable, inputEl) {
        // input dùng :checked + @change nên khi revert về giá trị cũ Vue không re-render:
        // phải set thẳng vào DOM element.
        const syncCheckbox = (checked) => {
          this.autoEnabled = checked;
          if (inputEl) inputEl.checked = checked;
        };

        const run = () => {
          this.request("vnx_cache_scheduler_toggle_auto", { enabled: enable ? 1 : 0 })
            .done((response) => {
              if (response.success) {
                syncCheckbox(response.data.auto_enabled);
                this.showAlert(enable ? "Đã bật tự động chạy" : "Đã tắt tự động chạy", "success");
              } else {
                syncCheckbox(!enable);
                this.showAlert(response.data || "Không thể cập nhật", "error");
              }
            })
            .fail(() => {
              syncCheckbox(!enable);
              this.showAlert("Lỗi kết nối", "error");
            });
        };

        if (!enable) {
          window
            .vnxConfirm({
              title: "Tắt tự động chạy",
              message: "Toàn bộ lịch hẹn sẽ tạm dừng tự động chạy cho tới khi bật lại. Trạng thái bật/tắt của từng lịch vẫn được giữ nguyên. Tiếp tục?",
              confirmText: "Tắt",
              tone: "warning",
            })
            .then((ok) => (ok ? run() : syncCheckbox(true)));
          return;
        }

        run();
      },

      saveNotify() {
        this.savingNotify = true;
        this.request("vnx_cache_scheduler_save_notify", {
          enabled: this.notify.enabled ? 1 : 0,
          webhook_url: this.notify.webhook_url,
        })
          .done((response) => {
            if (response.success) {
              this.notify = response.data;
              this.showNotify = false;
              this.showAlert("Đã lưu cài đặt thông báo", "success");
            } else {
              this.showAlert(response.data || "Lưu thất bại", "error");
            }
          })
          .fail(() => this.showAlert("Lỗi kết nối", "error"))
          .always(() => (this.savingNotify = false));
      },

      testNotify() {
        this.testingNotify = true;
        this.request("vnx_cache_scheduler_test_notify", { webhook_url: this.notify.webhook_url })
          .done((response) => {
            this.showAlert(response.data, response.success ? "success" : "error");
          })
          .fail(() => this.showAlert("Lỗi kết nối", "error"))
          .always(() => (this.testingNotify = false));
      },

      deleteJob(job) {
        this.request("vnx_cache_scheduler_delete", { id: job.id }).done((response) => {
          if (response.success) {
            this.jobs = this.normalizeJobs(response.data.jobs);
            this.showAlert("Đã xoá lịch hẹn", "success");
          }
        });
      },

      scheduleLabel(job) {
        if (job.schedule_type === "once") return "1 lần";
        if (job.schedule_type === "interval") return "Mỗi " + job.interval_minutes + " phút";
        if (job.schedule_type === "daily") return "Hằng ngày lúc " + job.time_of_day;
        if (job.schedule_type === "weekly") {
          const names = (job.weekdays || [])
            .map((d) => (this.weekdayOptions.find((o) => o.value === d) || {}).label)
            .filter(Boolean)
            .join(", ");
          return "Hằng tuần (" + names + ") lúc " + job.time_of_day;
        }
        return "—";
      },

      formatTime(ts) {
        if (!ts) return "—";

        return new Date(ts * 1000).toLocaleString("vi-VN", {
          hour12: false,
          hour: "2-digit",
          minute: "2-digit",
          day: "2-digit",
          month: "2-digit",
          year: "numeric",
        });
      },

      showAlert(message, type) {
        this.alertMessage = message;
        this.alertType = type || "success";

        // Alert nằm ở đầu panel nên phải cuộn tới, bấm nút trong bảng thì nó ngoài tầm nhìn.
        this.$nextTick(() => {
          const el = this.$el.querySelector('[role="alert"]');
          if (el && el.scrollIntoView) el.scrollIntoView({ behavior: "smooth", block: "nearest" });
        });

        window.clearTimeout(this.alertTimer);

        // Tin dài cần thêm thời gian đọc, chặn trên để tin ngắn khỏi nằm lì.
        const readTime = Math.min(12000, Math.round(String(message).length * 60));
        const timeout = (this.alertType === "error" ? 20000 : 18000) + readTime;

        this.alertTimer = window.setTimeout(() => {
          this.alertMessage = "";
        }, timeout);
      },
    },
  });
});
