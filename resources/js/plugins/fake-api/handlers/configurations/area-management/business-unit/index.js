import { fetchAreaBU } from '@db/configurations/area-management/business-unit/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerConfigurationsAreaBU = [
    // Get Area BU Details
    http.get(('/api/configurations/area-management-bu/search'), async ({ request }) => {
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
      const fetchData = await fetchAreaBU(page, itemsPerPage, queryLower, status);
      const dataRows = fetchData.data.rows;
      const totalActiveCount = fetchData.data.total_active || 0
      const totalNotActiveCount = fetchData.data.total_not_active || 0
      // Filter Area BU
      let filteredAreaBU = dataRows.filter(areaBU => (
        (areaBU.number?.toLowerCase().includes(queryLower) || 
        areaBU.description?.toLowerCase().includes(queryLower))
      )).reverse()

      // Sort Area BU
      if (sortByLocal) {
        if (sortByLocal === 'number') {
          filteredAreaBU = filteredAreaBU.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.number.localeCompare(b.number)
            else
              return b.number.localeCompare(a.number)
          })
        }

        if (sortByLocal === 'description') {
          filteredAreaBU = filteredAreaBU.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.description.localeCompare(b.description)
            else
              return b.description.localeCompare(a.description)
          })
        }
      }
      const totalAreaBU = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalAreaBU / itemsPerPageLocal)
      
      return HttpResponse.json({
        areaBU: filteredAreaBU,
        totalPages,
        totalAreaBU,
        totalActiveCount,
        totalNotActiveCount,
        page,
      }, { status: 200 })
    }),
]
