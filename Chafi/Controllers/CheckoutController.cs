// مسار الملف: Controllers/CheckoutController.cs

using System;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Microsoft.AspNetCore.Identity;
using Microsoft.AspNetCore.Authorization;
using Chafi.Models;

namespace Chafi.Controllers
{
    [Authorize] // يجب أن يكون مسجلاً للدخول لإتمام الطلب
    public class CheckoutController : Controller
    {
        private readonly ChafiDbContext _context;
        private readonly UserManager<ApplicationUser> _userManager;

        public CheckoutController(ChafiDbContext context, UserManager<ApplicationUser> userManager)
        {
            _context = context;
            _userManager = userManager;
        }

        // عرض صفحة الدفع والبيانات
        public async Task<IActionResult> Index()
        {
            var userId = _userManager.GetUserId(User);
            var cart = await _context.Carts
                .Include(c => c.Items).ThenInclude(i => i.Product)
                .Include(c => c.Items).ThenInclude(i => i.Package)
                .FirstOrDefaultAsync(c => c.UserId == userId);

            if (cart == null || !cart.Items.Any())
            {
                return RedirectToAction("Index", "Cart");
            }

            // هنا نقوم باستغلال بيانات المستخدم الموجودة مسبقاً (إن وجدت) لتعبئة الفورم
            var user = await _userManager.FindByIdAsync(userId);
            ViewBag.UserPhone = user.PhoneNumber;
            ViewBag.UserAddress = user.Address;
            ViewBag.UserCity = user.City;

            return View(cart);
        }

        // معالجة الطلب وتحويل عناصر السلة إلى Order & OrderItems & Payment
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> ProcessOrder(string address, string city, string phone, PaymentMethod paymentMethod)
        {
            var userId = _userManager.GetUserId(User);
            var cart = await _context.Carts
                .Include(c => c.Items).ThenInclude(i => i.Product)
                .Include(c => c.Items).ThenInclude(i => i.Package)
                .FirstOrDefaultAsync(c => c.UserId == userId);

            if (cart == null || !cart.Items.Any())
                return RedirectToAction("Index", "Cart");

            decimal shippingCost = cart.SubTotal >= 200 ? 0 : 25; // شحن مجاني فوق 200

            // 1. إنشاء الطلب (Order)
            var order = new Order
            {
                OrderNumber = "ORD-" + DateTime.Now.ToString("yyyyMMddHHmmss") + "-" + new Random().Next(100, 999),
                UserId = userId,
                SubTotal = cart.SubTotal,
                ShippingCost = shippingCost,
                TotalAmount = cart.SubTotal + shippingCost,
                Status = OrderStatus.Pending,
                ShippingAddress = address ?? "بدون عنوان",
                City = city ?? "غير محدد",
                Phone = phone ?? "0000000000",
                CreatedAt = DateTime.UtcNow
            };

            _context.Orders.Add(order);
            await _context.SaveChangesAsync(); // للحصول على OrderId

            // 2. إنشاء عناصر الطلب (OrderItems) من السلة
            foreach (var item in cart.Items)
            {
                var orderItem = new OrderItem
                {
                    OrderId = order.Id,
                    ProductId = item.ProductId,
                    PackageId = item.PackageId,
                    ItemName = item.ItemName,
                    UnitPrice = item.UnitPrice,
                    Quantity = item.Quantity,
                    TotalPrice = item.TotalPrice
                };
                _context.OrderItems.Add(orderItem);

                // تقليل المخزون (Stock) للمنتج إن وجد
                if (item.Product != null)
                {
                    item.Product.StockQuantity = Math.Max(0, item.Product.StockQuantity - item.Quantity);
                    item.Product.SalesCount += item.Quantity; // زيادة عداد المبيعات
                }
            }

            // 3. إنشاء سجل الدفع (Payment)
            var payment = new Payment
            {
                OrderId = order.Id,
                Amount = order.TotalAmount,
                Method = paymentMethod,
                Status = paymentMethod == PaymentMethod.CashOnDelivery ? PaymentStatus.Pending : PaymentStatus.Success, // كصورة مبدئية
                PaidAt = paymentMethod != PaymentMethod.CashOnDelivery ? DateTime.UtcNow : null
            };
            _context.Payments.Add(payment);

            // 4. إضافة سجل في التتبع (OrderStatusHistory)
            var history = new OrderStatusHistory
            {
                OrderId = order.Id,
                Status = OrderStatus.Pending,
                Notes = "تم إنشاء الطلب وجاري المراجعة"
            };
            _context.OrderStatusHistories.Add(history);

            // 5. إفراغ السلة بعد نجاح الطلب
            _context.CartItems.RemoveRange(cart.Items);

            await _context.SaveChangesAsync();

            return View("Success", order);
        }
    }
}