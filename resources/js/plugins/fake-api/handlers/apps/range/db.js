import { BASE_API_URL } from '@/plugins/1.router/additional-routes';
const token = useCookie('accessToken').value

export async function getListMerContractStatus(tblName,colName) {
  const url = `${BASE_API_URL}/api/apps/years-range/list`;
  const payload = {
    'tbl': tblName,
    'clmn': colName
  }

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
      const errorData = await response.json();
      throw new Error(`Error: ${response.status}, ${errorData.message || 'Unknown error'}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    throw new Error("Failed to fetching get range year data");
  }
}
