import { fetchBudgetBU } from '@db/apps/budgetBU/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerAppsBudgetBU = [
    // Get Budget BU Assigment Details
    http.get(('/api/apps/budget-bu/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const year = url.searchParams.get('year')
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
      const fetchData = await fetchBudgetBU(page, itemsPerPage, queryLower, year);
      const dataRows = fetchData.data.rows;
      const sumBudgetBU = fetchData.data.sum_budget;
      const sumExpenseBU = fetchData.data.sum_expense;
      const sumBalanceBU = fetchData.data.sum_balance;

      // Filter Budget BU
      let filteredBudgetBU = dataRows.filter(budgetBU => (
        (budgetBU.description.toLowerCase().includes(queryLower) || 
        budgetBU.number.toLowerCase().includes(queryLower)) && 
        budgetBU.join_first_bgt_year === (year || budgetBU.join_first_bgt_year)
      )).reverse()

      // Sort Budget BU
      if (sortByLocal) {
        if (sortByLocal === 'description') {
          filteredBudgetBU = filteredBudgetBU.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.description.localeCompare(b.description)
            else
              return b.description.localeCompare(a.description)
          })
        }
        if (sortByLocal === 'number') {
          filteredBudgetBU = filteredBudgetBU.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.number.localeCompare(b.number)
            else
              return b.number.localeCompare(a.number)
          })
        }
        if (sortByLocal === 'join_first_bgt_year') {
          filteredBudgetBU = filteredBudgetBU.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_first_bgt_year.localeCompare(b.join_first_bgt_year)
            else
              return b.join_first_bgt_year.localeCompare(a.join_first_bgt_year)
          })
        }
      }
      const totalBudgetBU = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalBudgetBU / itemsPerPageLocal)
      
      return HttpResponse.json({
        budgets: filteredBudgetBU,
        totalPages,
        totalBudgetBU,
        sumBudgetBU,
        sumExpenseBU,
        sumBalanceBU,
        page,
      }, { status: 200 })
    }),

    // Get Single Budget BU Detail
    http.get(('/api/apps/budget-bu/:id'), async ({ params }) => {
      const budgetBUId = Number(params.id)
      const budgetBU = db.budgets.find(e => e.id === budgetBUId)
      if (!budgetBU) {
        return HttpResponse.json({ message: 'Budget BU not found' }, { status: 404 })
      }
      else {
        return HttpResponse.json({
          ...budgetBU,
          ...{
            taskDone: 1230,
            projectDone: 568,
            taxId: 'Tax-8894',
            language: 'English',
          },
        }, { status: 200 })
      }
    }),

    // Delete Budget BU
    http.delete(('/api/apps/budget-bu/:id'), async ({ params }) => {
      const budgetBUId = Number(params.id)
      const budgetBUIndex = db.budgets.findIndex(e => e.id === budgetBUId)
      if (budgetBUIndex === -1) {
        return HttpResponse.json('Budget BU not found', { status: 404 })
      }
      else {
        db.budgets.splice(budgetBUIndex, 1)
        
        return new HttpResponse(null, {
          status: 204,
        })
      }
    }),

    // 👉 Add Budget BU
    http.post(('/api/apps/budget-bu'), async ({ request }) => {
      const budgetBU = await request.json()

      db.budgets.push({
        ...budgetBU,
        id: db.budgets.length + 1,
      })
      
      return HttpResponse.json({ body: budgetBU }, { status: 201 })
    }),
]