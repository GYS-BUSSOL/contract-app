import { BASE_API_URL } from '@/plugins/1.router/additional-routes';
const token = useCookie('accessToken').value

export async function fetchAreaBU(currentPage, rowPerPage, rowSearch, statusFilter) {
  let start = 0;
  const url = `${BASE_API_URL}/api/configurations/area-management-bu/search`;
  
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
        "value": "Business Unit",
      },
      {
        "name": "group_dimension",
        "logic_operator": "like",
        "value": "Business Unit",
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
    throw new Error("Failed to fetch mer business unit data");
  }
}
