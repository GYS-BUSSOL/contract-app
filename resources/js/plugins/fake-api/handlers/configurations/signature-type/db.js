import { BASE_API_URL } from '@/plugins/1.router/additional-routes';
const token = useCookie('accessToken').value

export async function fetchSignatureType(currentPage, rowPerPage, rowSearch) {
  let start = 0;
  const url = `${BASE_API_URL}/api/configurations/signature-type/search`;
  
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
          "name": "st_desc",
          "logic_operator": "like",
          "value": rowSearch
        },
      ]
    },
    "joins": [],
    "orders": {
      "columns": ["st_id"],
      "ascending": false
    }
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
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    throw new Error("Failed to fetching job type data");
  }
}
