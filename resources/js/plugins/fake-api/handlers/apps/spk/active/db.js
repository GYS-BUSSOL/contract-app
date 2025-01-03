
import { BASE_API_URL } from '@/plugins/1.router/additional-routes';
const token = useCookie('accessToken').value

export async function fetchSPKActive(currentPage, rowPerPage, rowSearch, vendorFilter, buFilter) {
  let start = 0;
  const url = `${BASE_API_URL}/api/apps/spk-active/search`;
  
  if(currentPage != 1 && currentPage > 1)
    start = (currentPage * rowPerPage) - rowPerPage

  const payload = {
    'vendor_name': vendorFilter,
    'cost_center': buFilter,
  };

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;

  } catch (error) {
    throw new Error("Failed to fetching SPK active data");
  }
}

