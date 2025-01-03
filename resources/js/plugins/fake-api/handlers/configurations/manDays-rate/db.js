import { BASE_API_URL } from '@/plugins/1.router/additional-routes';
const token = useCookie('accessToken').value

export async function fetchManDays(currentPage, rowPerPage, rowSearch, statusFilter) {
  let start = 0;
  const url = `${BASE_API_URL}/api/configurations/man-days-rate/search`;
  
  if(currentPage != 1 && currentPage > 1)
    start = (currentPage * rowPerPage) - rowPerPage

  const payload = {
    "paging": {
        "start": start,
        "length": rowPerPage
    },
    "columns": [],
    "group_column": {
      "operator": "AND",
      "group_operator": "OR",
      "where": [
        {
          "name": "rtk_id_jenis_tk",
          "logic_operator": "like",
          "value": rowSearch
        }
      ]
    },
    "joins": [],
    "orders": {
      "columns": ["rtk_id"],
      "ascending": true
    }
  };

  if(statusFilter) {
    payload['columns'].push(
      {
        "name": "rtk_active_status",
        "logic_operator": "=",
        "value": statusFilter,
      }
    )
  }
  
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(payload)
    })

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    throw new Error("Failed to fetching man days rate data");
  }
}
