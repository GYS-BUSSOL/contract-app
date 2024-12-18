import { BASE_API_URL } from '@/plugins/1.router/additional-routes';

export async function getListMerContractStatus(tblName,colName) {
  const url = `${BASE_API_URL}/api/apps/years-range/list`;
  // const token = localStorage.getItem('token') || 'YOUR_BEARER_TOKEN_HERE';
  const payload = {
    'tbl': tblName,
    'clmn': colName
  }

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
      const errorData = await response.json();
      throw new Error(`Error: ${response.status}, ${errorData.message || 'Unknown error'}`);
    }

    const data = await response.json();
    return data;

  } catch (error) {
    console.error("Error fetching get range year data");
    throw error;
  }
}
