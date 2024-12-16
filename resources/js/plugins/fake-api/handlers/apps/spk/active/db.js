
export async function fetchSPKActive(currentPage, rowPerPage, rowSearch, vendorFilter, buFilter, token) {
  let start = 0;
  const url = 'http://localhost:8000/api/apps/spk-active/search';
  
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
    console.error("Error fetching SPK active data:", error);
    throw error;
  }
}

