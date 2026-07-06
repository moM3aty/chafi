// مسار الملف: Controllers/ContactController.cs
using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Chafi.Models;

namespace Chafi.Controllers
{
    public class ContactController : Controller
    {
        private readonly ChafiDbContext _context;

        public ContactController(ChafiDbContext context)
        {
            _context = context;
        }

        // عرض صفحة تواصل معنا
        public IActionResult Index()
        {
            return View();
        }

        // استقبال رسائل الزوار وحفظها في قاعدة البيانات (Ajax)
        [HttpPost]
        public async Task<IActionResult> SubmitMessage([FromBody] ContactMessage model)
        {
            if (ModelState.IsValid)
            {
                model.CreatedAt = DateTime.UtcNow;
                // يمكننا تخزين الـ IP للعميل إن أردت لأسباب أمنية
                model.ClientIp = HttpContext.Connection.RemoteIpAddress?.ToString();

                _context.ContactMessages.Add(model);
                await _context.SaveChangesAsync();

                return Json(new { success = true, message = "تم إرسال رسالتك بنجاح! سنرد عليك في أقرب وقت ممكن." });
            }
            return Json(new { success = false, message = "يرجى ملء جميع الحقول المطلوبة بشكل صحيح." });
        }
    }
}