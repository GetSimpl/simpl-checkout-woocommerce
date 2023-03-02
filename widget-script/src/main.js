import { getWidgetConfig } from "./apiClient";
import { renderWidget } from "./renderWidget";
import { addGlobalSyles } from "./helper";
import { initiateCheckoutFrame } from "./checkoutFrame";

(() => {
  async function init() {
    addGlobalSyles();
    initiateCheckoutFrame();
    const widgetConfig = await getWidgetConfig();
    renderWidget(widgetConfig);
  }
  init();
})();
