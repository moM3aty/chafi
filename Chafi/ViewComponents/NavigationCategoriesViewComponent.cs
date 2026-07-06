using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Chafi.Models;

namespace Chafi.ViewComponents
{
    public class NavigationCategoriesViewComponent : ViewComponent
    {
        private readonly ChafiDbContext _context;

        public NavigationCategoriesViewComponent(ChafiDbContext context)
        {
            _context = context;
        }

        public async Task<IViewComponentResult> InvokeAsync()
        {
            var categories = await _context.Categories
                .Where(c => c.IsActive && c.ShowInMainMenu && c.ParentId == null)
                .OrderBy(c => c.DisplayOrder)
                .ToListAsync();

            return View(categories);
        }
    }
}