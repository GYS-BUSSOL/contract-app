export async function fetchJobType(currentPage, rowPerPage, rowSearch, statusFilter) {
  let start = 0;
  const url = 'http://localhost:8000/api/configurations/job-type/search';
  const token = 'YOUR_BEARER_TOKEN_HERE';
  
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
  };

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
    console.error("Error fetching job type data:", error);
    throw error;
  }
}