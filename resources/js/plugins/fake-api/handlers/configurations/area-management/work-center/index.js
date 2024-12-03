import { fetchAreaWC } from '@db/configurations/area-management/work-center/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerConfigurationsAreaWC = [
    // Get Area WC Details
    http.get(('/api/configurations/area-management-wc/search'), async ({ request }) => {
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
      const fetchData = await fetchAreaWC(page, itemsPerPage, queryLower, status);
      const dataRows = fetchData.data.rows;
      const totalActiveCount = fetchData.data.total_active || 0
      const totalNotActiveCount = fetchData.data.total_not_active || 0
      // Filter Area WC
      let filteredAreaWC = dataRows.filter(areaWC => (
        (areaWC.number?.toLowerCase().includes(queryLower) || 
        areaWC.description?.toLowerCase().includes(queryLower))
      )).reverse()

      // Sort Area WC
      if (sortByLocal) {
        if (sortByLocal === 'number') {
          filteredAreaWC = filteredAreaWC.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.number.localeCompare(b.number)
            else
              return b.number.localeCompare(a.number)
          })
        }

        if (sortByLocal === 'description') {
          filteredAreaWC = filteredAreaWC.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.description.localeCompare(b.description)
            else
              return b.description.localeCompare(a.description)
          })
        }
      }
      const totalAreaWC = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalAreaWC / itemsPerPageLocal)
      
      return HttpResponse.json({
        areaWC: filteredAreaWC,
        totalPages,
        totalAreaWC,
        totalActiveCount,
        totalNotActiveCount,
        page,
      }, { status: 200 })
    }),
]
