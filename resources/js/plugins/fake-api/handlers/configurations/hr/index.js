import { fetchHumanResources } from '@db/configurations/hr/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerConfigurationsHR = [
    // Get Human Resources Details
    http.get(('/api/configurations/human-resources/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const role = url.searchParams.get('role')
      const sortBy = url.searchParams.get('sortBy')
      const itemsPerPage = url.searchParams.get('itemsPerPage')
      const page = url.searchParams.get('page')
      const orderBy = url.searchParams.get('orderBy')
      const searchQuery = is.string(q) ? q : undefined
      let queryLower = (searchQuery ?? '').toString().toLowerCase()
      const parsedSortBy = destr(sortBy)
      const sortByLocal = is.string(parsedSortBy) ? parsedSortBy : ''
      const parsedOrderBy = destr(orderBy)
      const orderByLocal = is.string(parsedOrderBy) ? parsedOrderBy : ''
      const parsedItemsPerPage = destr(itemsPerPage)
      const itemsPerPageLocal = is.number(parsedItemsPerPage) ? parsedItemsPerPage : 10
      // Fetch Data
      const fetchData = await fetchHumanResources(page, itemsPerPage, queryLower, role);
      const dataRows = fetchData.data.rows;
      const totalActiveCount = fetchData.data.total_active || 0
      const totalNotActiveCount = fetchData.data.total_not_active || 0
      // Filter Human Resources
      let filteredUserHR = dataRows.filter(userHR => (
        (userHR.usr_name.toLowerCase().includes(queryLower) ||
        userHR.arr_business_unit?.some(bu => 
          bu.description.toLowerCase().includes(queryLower)) ||
        userHR.arr_business_unit?.some(bu => 
          bu.number.toLowerCase().includes(queryLower)) ||
        userHR.usr_display_name.toLowerCase().includes(queryLower)) &&
        userHR.usr_access === (role || userHR.usr_access)
      )).reverse()

      // Sort Human Resources
      if (sortByLocal) {
        if (sortByLocal === 'usr_name') {
          filteredUserHR = filteredUserHR.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.usr_name.localeCompare(b.usr_name)
            else
              return b.usr_name.localeCompare(a.usr_name)
          })
        }
        if (sortByLocal === 'usr_display_name') {
          filteredUserHR = filteredUserHR.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.usr_display_name.localeCompare(b.usr_display_name)
            else
              return b.usr_display_name.localeCompare(a.usr_display_name)
          })
        }
      }
      const totalUserHR = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalUserHR / itemsPerPageLocal)
      
      return HttpResponse.json({
        userHR: filteredUserHR,
        totalPages,
        totalUserHR,
        totalActiveCount,
        totalNotActiveCount,
        page,
      }, { status: 200 })
    }),
]
