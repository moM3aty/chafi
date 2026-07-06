// مسار الملف: Controllers/UserController.cs

using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Identity;
using Microsoft.EntityFrameworkCore;
using Chafi.Models;

namespace Chafi.Controllers
{
    [Authorize] // يجب أن يكون مسجلاً للدخول لرؤية حسابه
    public class UserController : Controller
    {
        private readonly ChafiDbContext _context;
        private readonly UserManager<ApplicationUser> _userManager;

        public UserController(ChafiDbContext context, UserManager<ApplicationUser> userManager)
        {
            _context = context;
            _userManager = userManager;
        }

        // لوحة تحكم العميل (طلباته، بياناته، ومشترياته الرقمية)
        public async Task<IActionResult> Dashboard()
        {
            var user = await _userManager.GetUserAsync(User);
            if (user == null) return Challenge();

            // جلب طلبات العميل
            var orders = await _context.Orders
                .Include(o => o.Items)
                .Where(o => o.UserId == user.Id)
                .OrderByDescending(o => o.CreatedAt)
                .ToListAsync();

            ViewBag.User = user;
            ViewBag.OrdersCount = orders.Count;
            ViewBag.TotalSpent = orders.Sum(o => o.TotalAmount);

            return View(orders);
        }

        // تحديث بيانات الملف الشخصي
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> UpdateProfile(string FullName, string PhoneNumber, string City, string Address)
        {
            var user = await _userManager.GetUserAsync(User);
            if (user != null)
            {
                user.FullName = FullName;
                user.PhoneNumber = PhoneNumber;
                user.City = City;
                user.Address = Address;

                await _userManager.UpdateAsync(user);
                TempData["SuccessMessage"] = "تم تحديث بياناتك بنجاح";
            }

            return RedirectToAction(nameof(Dashboard));
        }
    }
}