// مسار الملف: Controllers/WishlistController.cs

using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Identity;
using Microsoft.EntityFrameworkCore;
using Chafi.Models;

namespace Chafi.Controllers
{
    [Authorize]
    public class WishlistController : Controller
    {
        private readonly ChafiDbContext _context;
        private readonly UserManager<ApplicationUser> _userManager;

        public WishlistController(ChafiDbContext context, UserManager<ApplicationUser> userManager)
        {
            _context = context;
            _userManager = userManager;
        }

        // عرض المنتجات المحفوظة في المفضلة
        public async Task<IActionResult> Index()
        {
            var userId = _userManager.GetUserId(User);
            var wishlistItems = await _context.WishlistItems
                .Include(w => w.Product)
                .Where(w => w.UserId == userId)
                .OrderByDescending(w => w.AddedAt)
                .ToListAsync();

            return View(wishlistItems);
        }

        // إضافة أو إزالة منتج من المفضلة (Ajax)
        [HttpPost]
        public async Task<IActionResult> Toggle(int productId)
        {
            var userId = _userManager.GetUserId(User);
            if (string.IsNullOrEmpty(userId))
            {
                return Json(new { success = false, message = "يجب تسجيل الدخول أولاً" });
            }

            var existingItem = await _context.WishlistItems
                .FirstOrDefaultAsync(w => w.UserId == userId && w.ProductId == productId);

            bool isAdded = false;

            if (existingItem != null)
            {
                // إزالة إذا كان موجوداً مسبقاً
                _context.WishlistItems.Remove(existingItem);
            }
            else
            {
                // إضافة للمفضلة
                _context.WishlistItems.Add(new WishlistItem
                {
                    UserId = userId,
                    ProductId = productId,
                    AddedAt = System.DateTime.UtcNow
                });
                isAdded = true;
            }

            await _context.SaveChangesAsync();
            int newCount = await _context.WishlistItems.CountAsync(w => w.UserId == userId);

            return Json(new { success = true, added = isAdded, count = newCount });
        }

        [HttpPost]
        public async Task<IActionResult> Remove(int id)
        {
            var userId = _userManager.GetUserId(User);
            var item = await _context.WishlistItems.FirstOrDefaultAsync(w => w.Id == id && w.UserId == userId);
            if (item != null)
            {
                _context.WishlistItems.Remove(item);
                await _context.SaveChangesAsync();
            }
            return RedirectToAction(nameof(Index));
        }
    }
}