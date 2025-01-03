import { BASE_API_URL } from '@/plugins/1.router/additional-routes';
const token = useCookie('accessToken').value

export async function fetchJobType(currentPage, rowPerPage, rowSearch, statusFilter) {
  let start = 0;
  const url = `${BASE_API_URL}/api/configurations/job-type/search`;
  
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
          "name": "job_type",
          "logic_operator": "like",
          "value": rowSearch
        },
      ]
    },
    "joins": [],
    "orders": {
      "columns": ["job_type_id"],
      "ascending": false
    }
  }

  if(statusFilter) {
    payload['columns'].push(
      {
        "name": "job_is_active",
        "logic_operator": statusFilter == 'null' ? "ISNULL" :  '=',
        "value": statusFilter == 'null' ? "NULL" : statusFilter,
      },
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
