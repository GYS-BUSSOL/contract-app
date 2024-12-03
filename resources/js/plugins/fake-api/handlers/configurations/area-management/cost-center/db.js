export async function fetchAreaCC(currentPage, rowPerPage, rowSearch, statusFilter) {
  let start = 0;
  const url = 'http://localhost:8000/api/configurations/area-management-cc/search';
  const token = 'YOUR_BEARER_TOKEN_HERE';
  
  if(currentPage != 1 && currentPage > 1)
    start = (currentPage * rowPerPage) - rowPerPage

  const payload = {
    "paging": {
        "start": start,
        "length": rowPerPage
    },
    "columns": [
      {
        "name": "group_dimension",
        "logic_operator": "=",
        "value": "Cost Center",
      },
      {
        "name": "group_dimension",
        "logic_operator": "like",
        "value": "Cost Center",
      },
    ],
    "group_column": {
      "operator": "AND",
      "group_operator": "OR",
      "where": [
        {
          "name": "number",
          "logic_operator": "like",
          "value": rowSearch
        },
        {
          "name": "description",
          "logic_operator": "like",
          "value": rowSearch
        },
      ]
    },
    "joins": [],
    "orders": {
      "columns": ["id"],
      "ascending": true
    }
  };

  if(statusFilter) {
    payload['columns'].push(
      {
        "name": "is_active",
        "logic_operator": statusFilter == 0 ? "!=" : (statusFilter == 'null' ? 'ISNULL' : '='),
        "value": statusFilter == 0 ? "1" : statusFilter,
      },
      {
        "name": "is_active",
        "logic_operator": statusFilter == 0 ? "=" : (statusFilter == 'null' ? 'ISNULL' : '='),
        "value": statusFilter == 0 ? "0" : statusFilter,
      },
      {
        "name": "is_active",
        "logic_operator": statusFilter == 0 ? "NOTNULL" : (statusFilter == 'null' ? 'ISNULL' : '='),
        "value": statusFilter == 0 ? "NOTNULL" : statusFilter,
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
    console.error("Error fetching cost center data:", error);
    throw error;
  }
}
