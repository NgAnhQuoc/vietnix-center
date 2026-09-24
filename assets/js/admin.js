import "preline";
import VNXGeneral from "./admin/general";
import VNXToolPage from "./admin/tool_page";
import VNXConfirm from "./admin/confirm";
import VNXHelpDrawer from "./admin/help_drawer";
import "./admin/vietnix_utm_tracker"
import "./admin/vietnix_sync_telegram_sheet"
import "./admin/vietnix_api"
window.addEventListener("DOMContentLoaded", (event) => {
  // Init truoc cac module khac: no chan click o pha capture, phai san sang som.
  VNXConfirm.init();
  VNXGeneral.init();
  VNXToolPage.init();
  VNXHelpDrawer.init();
});
