import { fetchManDays } from '@db/configurations/manDays-rate/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerConfigurationsManDays = [
    // Get Man Days Rate Details
    http.get(('/api/configurations/man-days-rate/search'), async ({ request }) => {
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
      const fetchData = await fetchManDays(page, itemsPerPage, queryLower, status);
      const dataRows = fetchData.data.rows;
      const totalActiveCount = fetchData.data.total_active || 0
      const totalNotActiveCount = fetchData.data.total_not_active || 0
      // Filter Man Days Rate
      let filteredManDaysRate = dataRows.filter(manDays => (
        (manDays.rtk_id_jenis_tk.toLowerCase().includes(queryLower))
      )).reverse()

      // Sort Man Days Rate
      if (sortByLocal) {
        if (sortByLocal === 'rtk_id_jenis_tk') {
          filteredManDaysRate = filteredManDaysRate.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.rtk_id_jenis_tk.localeCompare(b.rtk_id_jenis_tk)
            else
              return b.rtk_id_jenis_tk.localeCompare(a.rtk_id_jenis_tk)
          })
        }
      }
      const totalManDays = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalManDays / itemsPerPageLocal)
      
      return HttpResponse.json({
        manDays: filteredManDaysRate,
        totalPages,
        totalManDays,
        totalActiveCount,
        totalNotActiveCount,
        page,
      }, { status: 200 })
    }),
]
