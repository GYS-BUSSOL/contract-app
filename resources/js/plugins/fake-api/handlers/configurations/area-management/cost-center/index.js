import { fetchAreaCC } from '@db/configurations/area-management/cost-center/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerConfigurationsAreaCC = [
    // Get Area CC Details
    http.get(('/api/configurations/area-management-cc/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const status = url.searchParams.get('status')
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
      const fetchData = await fetchAreaCC(page, itemsPerPage, queryLower, status);
      const dataRows = fetchData.data.rows;
      const totalActiveCount = fetchData.data.total_active || 0
      const totalNotActiveCount = fetchData.data.total_not_active || 0
      // Filter Area CC
      let filteredAreaCC = dataRows.filter(areaCC => (
        (areaCC.number?.toLowerCase().includes(queryLower) || 
        areaCC.description?.toLowerCase().includes(queryLower))
      )).reverse()

      // Sort Area CC
      if (sortByLocal) {
        if (sortByLocal === 'number') {
          filteredAreaCC = filteredAreaCC.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.number.localeCompare(b.number)
            else
              return b.number.localeCompare(a.number)
          })
        }

        if (sortByLocal === 'description') {
          filteredAreaCC = filteredAreaCC.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.description.localeCompare(b.description)
            else
              return b.description.localeCompare(a.description)
          })
        }
      }
      const totalAreaCC = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalAreaCC / itemsPerPageLocal)
      
      return HttpResponse.json({
        areaCC: filteredAreaCC,
        totalPages,
        totalAreaCC,
        totalActiveCount,
        totalNotActiveCount,
        page,
      }, { status: 200 })
    }),
]
