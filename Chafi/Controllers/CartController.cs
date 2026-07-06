// مسار الملف: Controllers/CartController.cs

using System;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Microsoft.AspNetCore.Identity;
using Chafi.Models;

namespace Chafi.Controllers
{
    public class CartController : Controller
    {
        private readonly ChafiDbContext _context;
        private readonly UserManager<ApplicationUser> _userManager;

        public CartController(ChafiDbContext context, UserManager<ApplicationUser> userManager)
        {
            _context = context;
            _userManager = userManager;
        }

        // عرض محتويات السلة
        public async Task<IActionResult> Index()
        {
            var userId = _userManager.GetUserId(User);
            if (string.IsNullOrEmpty(userId))
            {
                // إذا لم يكن مسجلاً الدخول، نوجهه لصفحة الدخول
                return Redirect("/Identity/Account/Login");
            }

            var cart = await _context.Carts
                .Include(c => c.Items)
                    .ThenInclude(i => i.Product)
                .Include(c => c.Items)
                    .ThenInclude(i => i.Package)
                .FirstOrDefaultAsync(c => c.UserId == userId);

            if (cart == null)
            {
                cart = new Cart { UserId = userId };
                _context.Carts.Add(cart);
                await _context.SaveChangesAsync();
            }

            return View(cart);
        }

        // إضافة منتج، باقة، فيديو، أو صوتيات للسلة
        [HttpPost]
        public async Task<IActionResult> AddToCart(int? productId, int? packageId, int? audioId, int? videoId, int quantity = 1)
        {
            var userId = _userManager.GetUserId(User);
            if (string.IsNullOrEmpty(userId))
            {
                return Json(new { success = false, message = "يرجى تسجيل الدخول أولاً." });
            }

            var cart = await _context.Carts
                .Include(c => c.Items)
                .FirstOrDefaultAsync(c => c.UserId == userId);

            if (cart == null)
            {
                cart = new Cart { UserId = userId };
                _context.Carts.Add(cart);
                await _context.SaveChangesAsync();
            }

            // التحقق من وجود العنصر مسبقاً لزيادة الكمية
            var existingItem = cart.Items.FirstOrDefault(i =>
                (productId.HasValue && i.ProductId == productId) ||
                (packageId.HasValue && i.PackageId == packageId));

            if (existingItem != null)
            {
                existingItem.Quantity += quantity;
            }
            else
            {
                var newItem = new CartItem
                {
                    CartUserId = userId,
                    ProductId = productId,
                    PackageId = packageId,
                    Quantity = quantity,
                    AddedAt = DateTime.UtcNow
                };
                // ملاحظة: إذا كان صوتيات أو فيديو يمكن إضافتهم كمنتجات رقمية لاحقاً في الـ Database

                _context.CartItems.Add(newItem);
            }

            cart.UpdatedAt = DateTime.UtcNow;
            await _context.SaveChangesAsync();

            // يمكن إعادة التوجيه للسلة أو إرجاع JSON إذا كنت تستخدم Ajax
            return RedirectToAction(nameof(Index));
        }

        [HttpPost]
        public async Task<IActionResult> RemoveFromCart(int itemId)
        {
            var item = await _context.CartItems.FindAsync(itemId);
            if (item != null)
            {
                _context.CartItems.Remove(item);
                await _context.SaveChangesAsync();
            }
            return RedirectToAction(nameof(Index));
        }
    }
}