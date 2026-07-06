// مسار الملف: Controllers/AdminController.cs

using System;
using System.IO;
using System.Linq;
using System.Threading.Tasks;
using System.Collections.Generic;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.Rendering;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Hosting;
using Microsoft.EntityFrameworkCore;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Identity;
using Chafi.Models;

namespace Chafi.Controllers
{
    [Authorize(Roles = "Admin,SuperAdmin")]
    public class AdminController : Controller
    {
        private readonly ChafiDbContext _context;
        private readonly IWebHostEnvironment _webHostEnvironment;
        private readonly UserManager<ApplicationUser> _userManager;
        private readonly RoleManager<IdentityRole> _roleManager;

        public AdminController(ChafiDbContext context, IWebHostEnvironment webHostEnvironment, UserManager<ApplicationUser> userManager, RoleManager<IdentityRole> roleManager)
        {
            _context = context;
            _webHostEnvironment = webHostEnvironment;
            _userManager = userManager;
            _roleManager = roleManager;
        }

        // ================= الرئيسية (Dashboard) ================= //
        public async Task<IActionResult> Index()
        {
            ViewBag.CategoriesCount = await _context.Categories.CountAsync();
            ViewBag.ProductsCount = await _context.Products.CountAsync();
            ViewBag.OrdersCount = await _context.Orders.CountAsync();
            ViewBag.AudiosCount = await _context.Audios.CountAsync();
            ViewBag.VideosCount = await _context.Videos.CountAsync();

            ViewBag.MessagesCount = await _context.ContactMessages.CountAsync(m => !m.IsRead);
            ViewBag.ReviewsCount = await _context.Reviews.CountAsync(r => !r.IsApproved);

            return View();
        }

        // ================= الأقسام (Categories) ================= //
        public async Task<IActionResult> Categories()
        {
            return View(await _context.Categories.Include(c => c.Parent).OrderBy(c => c.DisplayOrder).ToListAsync());
        }

        public IActionResult CreateCategory()
        {
            ViewBag.ParentId = new SelectList(_context.Categories.Where(c => c.ParentId == null), "Id", "Name");
            return View();
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> CreateCategory(Category category, IFormFile ImageFile)
        {
            if (ModelState.IsValid)
            {
                if (ImageFile != null && ImageFile.Length > 0)
                {
                    string uploadsFolder = Path.Combine(_webHostEnvironment.WebRootPath, "uploads", "categories");
                    Directory.CreateDirectory(uploadsFolder);
                    string uniqueFileName = Guid.NewGuid().ToString() + "_" + Path.GetFileName(ImageFile.FileName);
                    string filePath = Path.Combine(uploadsFolder, uniqueFileName);
                    using (var fileStream = new FileStream(filePath, FileMode.Create))
                    {
                        await ImageFile.CopyToAsync(fileStream);
                    }
                    category.ImageUrl = "/uploads/categories/" + uniqueFileName;
                }

                if (string.IsNullOrEmpty(category.Slug)) { category.Slug = Guid.NewGuid().ToString(); }

                _context.Add(category);
                await _context.SaveChangesAsync();
                return RedirectToAction(nameof(Categories));
            }
            ViewBag.ParentId = new SelectList(_context.Categories.Where(c => c.ParentId == null), "Id", "Name", category.ParentId);
            return View(category);
        }

        // ================= المنتجات (Products) ================= //
        public async Task<IActionResult> Products()
        {
            return View(await _context.Products.Include(p => p.Category).OrderByDescending(p => p.CreatedAt).ToListAsync());
        }

        public IActionResult CreateProduct()
        {
            ViewBag.CategoryId = new SelectList(_context.Categories.Where(c => c.IsActive), "Id", "Name");
            return View();
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> CreateProduct(Product product, IFormFile ImageFile)
        {
            if (ModelState.IsValid)
            {
                if (ImageFile != null && ImageFile.Length > 0)
                {
                    string uploadsFolder = Path.Combine(_webHostEnvironment.WebRootPath, "uploads", "products");
                    Directory.CreateDirectory(uploadsFolder);
                    string uniqueFileName = Guid.NewGuid().ToString() + "_" + Path.GetFileName(ImageFile.FileName);
                    string filePath = Path.Combine(uploadsFolder, uniqueFileName);
                    using (var fileStream = new FileStream(filePath, FileMode.Create))
                    {
                        await ImageFile.CopyToAsync(fileStream);
                    }
                    product.ImageUrl = "/uploads/products/" + uniqueFileName;
                }

                if (string.IsNullOrEmpty(product.Slug)) { product.Slug = Guid.NewGuid().ToString(); }

                _context.Add(product);
                await _context.SaveChangesAsync();
                return RedirectToAction(nameof(Products));
            }
            ViewBag.CategoryId = new SelectList(_context.Categories.Where(c => c.IsActive), "Id", "Name", product.CategoryId);
            return View(product);
        }

        public async Task<IActionResult> EditProduct(int id)
        {
            var product = await _context.Products.FindAsync(id);
            if (product == null) return NotFound();

            ViewBag.CategoryId = new SelectList(_context.Categories.Where(c => c.IsActive), "Id", "Name", product.CategoryId);
            return View(product);
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> EditProduct(Product product, IFormFile ImageFile)
        {
            if (ModelState.IsValid)
            {
                if (ImageFile != null && ImageFile.Length > 0)
                {
                    string uploadsFolder = Path.Combine(_webHostEnvironment.WebRootPath, "uploads", "products");
                    Directory.CreateDirectory(uploadsFolder);
                    string uniqueFileName = Guid.NewGuid().ToString() + "_" + Path.GetFileName(ImageFile.FileName);
                    string filePath = Path.Combine(uploadsFolder, uniqueFileName);
                    using (var fileStream = new FileStream(filePath, FileMode.Create))
                    {
                        await ImageFile.CopyToAsync(fileStream);
                    }
                    product.ImageUrl = "/uploads/products/" + uniqueFileName;
                }

                _context.Update(product);
                await _context.SaveChangesAsync();
                return RedirectToAction(nameof(Products));
            }
            ViewBag.CategoryId = new SelectList(_context.Categories.Where(c => c.IsActive), "Id", "Name", product.CategoryId);
            return View(product);
        }

        // ================= الصوتيات (Audios) ================= //
        public async Task<IActionResult> Audios()
        {
            return View(await _context.Audios.OrderByDescending(a => a.CreatedAt).ToListAsync());
        }

        public IActionResult CreateAudio()
        {
            ViewBag.CategoryId = new SelectList(_context.Categories.Where(c => c.IsActive), "Id", "Name");
            return View();
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> CreateAudio(Audio audio, IFormFile AudioFile, IFormFile ThumbnailFile)
        {
            if (ModelState.IsValid)
            {
                if (AudioFile != null && AudioFile.Length > 0)
                {
                    string uploadsFolder = Path.Combine(_webHostEnvironment.WebRootPath, "uploads", "audios");
                    Directory.CreateDirectory(uploadsFolder);
                    string uniqueFileName = Guid.NewGuid().ToString() + "_" + Path.GetFileName(AudioFile.FileName);
                    string filePath = Path.Combine(uploadsFolder, uniqueFileName);
                    using (var fileStream = new FileStream(filePath, FileMode.Create))
                    {
                        await AudioFile.CopyToAsync(fileStream);
                    }
                    audio.AudioUrl = "/uploads/audios/" + uniqueFileName;
                }

                if (ThumbnailFile != null && ThumbnailFile.Length > 0)
                {
                    string uploadsFolder = Path.Combine(_webHostEnvironment.WebRootPath, "uploads", "audios", "thumbs");
                    Directory.CreateDirectory(uploadsFolder);
                    string uniqueFileName = Guid.NewGuid().ToString() + "_" + Path.GetFileName(ThumbnailFile.FileName);
                    string filePath = Path.Combine(uploadsFolder, uniqueFileName);
                    using (var fileStream = new FileStream(filePath, FileMode.Create))
                    {
                        await ThumbnailFile.CopyToAsync(fileStream);
                    }
                    audio.ThumbnailUrl = "/uploads/audios/thumbs/" + uniqueFileName;
                }

                _context.Add(audio);
                await _context.SaveChangesAsync();
                return RedirectToAction(nameof(Audios));
            }
            ViewBag.CategoryId = new SelectList(_context.Categories.Where(c => c.IsActive), "Id", "Name", audio.CategoryId);
            return View(audio);
        }

        // ================= الفيديوهات (Videos) ================= //
        public async Task<IActionResult> Videos()
        {
            return View(await _context.Videos.OrderByDescending(v => v.CreatedAt).ToListAsync());
        }

        public IActionResult CreateVideo()
        {
            ViewBag.CategoryId = new SelectList(_context.Categories.Where(c => c.IsActive), "Id", "Name");
            return View();
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> CreateVideo(Video video, IFormFile VideoFile, IFormFile ThumbnailFile)
        {
            if (ModelState.IsValid)
            {
                if (VideoFile != null && VideoFile.Length > 0)
                {
                    string uploadsFolder = Path.Combine(_webHostEnvironment.WebRootPath, "uploads", "videos");
                    Directory.CreateDirectory(uploadsFolder);
                    string uniqueFileName = Guid.NewGuid().ToString() + "_" + Path.GetFileName(VideoFile.FileName);
                    string filePath = Path.Combine(uploadsFolder, uniqueFileName);
                    using (var fileStream = new FileStream(filePath, FileMode.Create))
                    {
                        await VideoFile.CopyToAsync(fileStream);
                    }
                    video.VideoUrl = "/uploads/videos/" + uniqueFileName;
                }

                if (ThumbnailFile != null && ThumbnailFile.Length > 0)
                {
                    string uploadsFolder = Path.Combine(_webHostEnvironment.WebRootPath, "uploads", "videos", "thumbs");
                    Directory.CreateDirectory(uploadsFolder);
                    string uniqueFileName = Guid.NewGuid().ToString() + "_" + Path.GetFileName(ThumbnailFile.FileName);
                    string filePath = Path.Combine(uploadsFolder, uniqueFileName);
                    using (var fileStream = new FileStream(filePath, FileMode.Create))
                    {
                        await ThumbnailFile.CopyToAsync(fileStream);
                    }
                    video.ThumbnailUrl = "/uploads/videos/thumbs/" + uniqueFileName;
                }

                _context.Add(video);
                await _context.SaveChangesAsync();
                return RedirectToAction(nameof(Videos));
            }
            ViewBag.CategoryId = new SelectList(_context.Categories.Where(c => c.IsActive), "Id", "Name", video.CategoryId);
            return View(video);
        }

        // ================= الباقات (Packages) ================= //
        public async Task<IActionResult> Packages()
        {
            return View(await _context.Packages.OrderByDescending(p => p.CreatedAt).ToListAsync());
        }

        public IActionResult CreatePackage()
        {
            return View();
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> CreatePackage(Package package, IFormFile ImageFile)
        {
            if (ModelState.IsValid)
            {
                if (ImageFile != null && ImageFile.Length > 0)
                {
                    string uploadsFolder = Path.Combine(_webHostEnvironment.WebRootPath, "uploads", "packages");
                    Directory.CreateDirectory(uploadsFolder);
                    string uniqueFileName = Guid.NewGuid().ToString() + "_" + Path.GetFileName(ImageFile.FileName);
                    string filePath = Path.Combine(uploadsFolder, uniqueFileName);
                    using (var fileStream = new FileStream(filePath, FileMode.Create))
                    {
                        await ImageFile.CopyToAsync(fileStream);
                    }
                    package.ImageUrl = "/uploads/packages/" + uniqueFileName;
                }

                _context.Add(package);
                await _context.SaveChangesAsync();
                return RedirectToAction(nameof(Packages));
            }
            return View(package);
        }

        // ================= الطلبات (Orders) ================= //
        public async Task<IActionResult> Orders()
        {
            return View(await _context.Orders.Include(o => o.User).OrderByDescending(o => o.CreatedAt).ToListAsync());
        }

        public async Task<IActionResult> OrderDetails(int id)
        {
            var order = await _context.Orders
                .Include(o => o.User)
                .Include(o => o.Items)
                .FirstOrDefaultAsync(o => o.Id == id);

            if (order == null) return NotFound();

            return View(order);
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> UpdateOrderStatus(int orderId, OrderStatus newStatus)
        {
            var order = await _context.Orders.FindAsync(orderId);
            if (order != null)
            {
                order.Status = newStatus;

                var history = new OrderStatusHistory
                {
                    OrderId = orderId,
                    Status = newStatus,
                    Notes = "تم تحديث الحالة من لوحة الإدارة"
                };
                _context.OrderStatusHistories.Add(history);

                await _context.SaveChangesAsync();
            }
            return RedirectToAction(nameof(OrderDetails), new { id = orderId });
        }

        // ================= رسائل التواصل (Contact Messages) ================= //
        public async Task<IActionResult> Messages()
        {
            return View(await _context.ContactMessages.OrderByDescending(m => m.CreatedAt).ToListAsync());
        }

        public async Task<IActionResult> ReadMessage(int id)
        {
            var msg = await _context.ContactMessages.FindAsync(id);
            if (msg == null) return NotFound();

            if (!msg.IsRead)
            {
                msg.IsRead = true;
                msg.ReadAt = DateTime.UtcNow;
                await _context.SaveChangesAsync();
            }
            return View(msg);
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> DeleteMessage(int id)
        {
            var msg = await _context.ContactMessages.FindAsync(id);
            if (msg != null)
            {
                _context.ContactMessages.Remove(msg);
                await _context.SaveChangesAsync();
            }
            return RedirectToAction(nameof(Messages));
        }

        // ================= الإعلانات (Advertisements) ================= //
        public async Task<IActionResult> Advertisements()
        {
            return View(await _context.Advertisements.OrderByDescending(a => a.CreatedAt).ToListAsync());
        }

        public IActionResult CreateAdvertisement()
        {
            return View();
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> CreateAdvertisement(Advertisement ad, IFormFile ImageFile)
        {
            if (ModelState.IsValid)
            {
                if (ImageFile != null && ImageFile.Length > 0)
                {
                    string uploadsFolder = Path.Combine(_webHostEnvironment.WebRootPath, "uploads", "ads");
                    Directory.CreateDirectory(uploadsFolder);
                    string uniqueFileName = Guid.NewGuid().ToString() + "_" + Path.GetFileName(ImageFile.FileName);
                    string filePath = Path.Combine(uploadsFolder, uniqueFileName);
                    using (var fileStream = new FileStream(filePath, FileMode.Create))
                    {
                        await ImageFile.CopyToAsync(fileStream);
                    }
                    ad.ImageUrl = "/uploads/ads/" + uniqueFileName;
                }
                else
                {
                    ModelState.AddModelError("ImageUrl", "صورة الإعلان مطلوبة");
                    return View(ad);
                }

                _context.Add(ad);
                await _context.SaveChangesAsync();
                return RedirectToAction(nameof(Advertisements));
            }
            return View(ad);
        }

        // ================= التقييمات وآراء العملاء (Reviews) ================= //
        public async Task<IActionResult> Reviews()
        {
            return View(await _context.Reviews.Include(r => r.Product).Include(r => r.User).OrderByDescending(r => r.CreatedAt).ToListAsync());
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> ApproveReview(int id)
        {
            var review = await _context.Reviews.FindAsync(id);
            if (review != null)
            {
                review.IsApproved = true;
                await _context.SaveChangesAsync();
            }
            return RedirectToAction(nameof(Reviews));
        }

        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> DeleteReview(int id)
        {
            var review = await _context.Reviews.FindAsync(id);
            if (review != null)
            {
                _context.Reviews.Remove(review);
                await _context.SaveChangesAsync();
            }
            return RedirectToAction(nameof(Reviews));
        }

        // ================= العروض والخصومات (Offers) ================= //
        public async Task<IActionResult> Offers()
        {
            return View(await _context.Offers.OrderByDescending(o => o.CreatedAt).ToListAsync());
        }

        // ================= النشرة البريدية (Newsletter) ================= //
        public async Task<IActionResult> NewsletterSubscriptions()
        {
            return View(await _context.NewsletterSubscriptions.OrderByDescending(n => n.SubscribedAt).ToListAsync());
        }

        // ================= إعدادات الموقع (Site Settings) ================= //
        public async Task<IActionResult> SiteSettings()
        {
            return View(await _context.SiteSettings.ToListAsync());
        }

        // ================= إدارة الصلاحيات للمستخدمين (Role Management) ================= //
        [Authorize(Roles = "SuperAdmin")]
        public async Task<IActionResult> ManageRoles()
        {
            var users = await _userManager.Users.ToListAsync();
            var userRoles = new Dictionary<string, IList<string>>();

            foreach (var user in users)
            {
                var roles = await _userManager.GetRolesAsync(user);
                userRoles.Add(user.Id, roles);
            }

            ViewBag.UserRoles = userRoles;
            return View(users);
        }

        [HttpPost]
        [Authorize(Roles = "SuperAdmin")]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> AssignRole(string userId, string roleName)
        {
            var user = await _userManager.FindByIdAsync(userId);
            if (user != null)
            {
                if (!await _roleManager.RoleExistsAsync(roleName))
                {
                    await _roleManager.CreateAsync(new IdentityRole(roleName));
                }

                if (!await _userManager.IsInRoleAsync(user, roleName))
                {
                    await _userManager.AddToRoleAsync(user, roleName);
                }
            }
            return RedirectToAction(nameof(ManageRoles));
        }
    }
}