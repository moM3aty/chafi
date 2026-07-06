// مسار الملف: Controllers/AccountController.cs

using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Identity;
using Chafi.Models;
using Chafi.ViewModels;

namespace Chafi.Controllers
{
    public class AccountController : Controller
    {
        private readonly UserManager<ApplicationUser> _userManager;
        private readonly SignInManager<ApplicationUser> _signInManager;

        public AccountController(UserManager<ApplicationUser> userManager, SignInManager<ApplicationUser> signInManager)
        {
            _userManager = userManager;
            _signInManager = signInManager;
        }

        // هذا الـ Action يستخدم للتسجيل عبر الـ Modal بـ Ajax
        [HttpPost]
        public async Task<IActionResult> RegisterAjax([FromBody] RegisterViewModel model)
        {
            if (ModelState.IsValid)
            {
                var user = new ApplicationUser
                {
                    UserName = model.Email,
                    Email = model.Email,
                    FullName = model.FullName,
                    PhoneNumber = model.PhoneNumber
                };

                var result = await _userManager.CreateAsync(user, model.Password);

                if (result.Succeeded)
                {
                    await _signInManager.SignInAsync(user, isPersistent: false);
                    return Json(new { success = true, message = "تم إنشاء الحساب بنجاح" });
                }

                return Json(new { success = false, errors = result.Errors.Select(e => e.Description) });
            }

            return Json(new { success = false, message = "البيانات المدخلة غير صحيحة" });
        }

        // هذا الـ Action يستخدم لتسجيل الدخول عبر الـ Modal بـ Ajax
        [HttpPost]
        public async Task<IActionResult> LoginAjax([FromBody] LoginViewModel model)
        {
            if (ModelState.IsValid)
            {
                var result = await _signInManager.PasswordSignInAsync(model.Email, model.Password, model.RememberMe, lockoutOnFailure: false);

                if (result.Succeeded)
                {
                    return Json(new { success = true, message = "تم تسجيل الدخول بنجاح" });
                }

                return Json(new { success = false, message = "البريد الإلكتروني أو كلمة المرور غير صحيحة" });
            }

            return Json(new { success = false, message = "البيانات المدخلة غير صحيحة" });
        }

        // تسجيل الخروج
        [HttpPost]
        public async Task<IActionResult> Logout()
        {
            await _signInManager.SignOutAsync();
            return RedirectToAction("Index", "Home");
        }
    }
}