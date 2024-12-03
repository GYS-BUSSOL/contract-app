export async function fetchHumanResources(currentPage, rowPerPage, rowSearch, roleFilter) {
  let start = 0;
  const url = 'http://localhost:8000/api/configurations/human-resources/search';
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
        "name": "is_active",
        "logic_operator": "=",
        "value": 0
      },
    ],
    "group_column": {
      "operator": "AND",
      "group_operator": "OR",
      "where": [
        {
          "name": "usr_name",
          "logic_operator": "like",
          "value": rowSearch
        },
        {
          "name": "usr_display_name",
          "logic_operator": "like",
          "value": rowSearch
        },
      ]
    },
    "joins": [],
    "orders": {
      "columns": ["usr_id"],
      "ascending": false
    }
  };

  if(roleFilter) {
    payload['columns'].push(
      {
        "name": "usr_access",
        "logic_operator": "=",
        "value": roleFilter,
      }
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
    console.error("Error fetching Human Resources data:", error);
    throw error;
  }
}
