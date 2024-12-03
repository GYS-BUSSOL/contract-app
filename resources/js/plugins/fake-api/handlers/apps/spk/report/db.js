
export async function fetchSPKReport(currentPage, rowPerPage, rowSearch, vendorFilter, buFilter, startDateFilter, endDateFilter, spkStatus) {
  let start = 0;
  const url = 'http://localhost:8000/api/apps/spk-report/search';
  const token = 'YOUR_BEARER_TOKEN_HERE';
  
  if(currentPage != 1 && currentPage > 1)
    start = (currentPage * rowPerPage) - rowPerPage

  const now = new Date();
  const startDate = startDateFilter == '' ? now : new Date(startDateFilter);
  const endDate = endDateFilter == '' ? now : new Date(endDateFilter);
  const formatStartDate = `${startDate.getFullYear()}-${String(startDate.getMonth() + 1).padStart(2, '0')}`;
  const formatEndDate = `${endDate.getFullYear()}-${String(endDate.getMonth() + 1).padStart(2, '0')}`;
  
  const payload = {
    "start_date": formatStartDate,
    'end_date': formatEndDate,
    'vendor_name': vendorFilter,
    'cost_center': buFilter,
    'spk_status': spkStatus
  };

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        // 'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;

  } catch (error) {
    console.error("Error fetching SPK report data:", error);
    throw error;
  }
}

