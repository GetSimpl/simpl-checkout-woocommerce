const WIDGET_CONFIG_URL = "http://localhost:3000/config";

export const getWidgetConfig = async () => {
  const headers = {
    "Content-Type": "application/json",
  };
  const res = await fetch(WIDGET_CONFIG_URL, headers);
  return await res.json();
};
