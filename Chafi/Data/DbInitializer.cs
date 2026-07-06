using System;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Builder;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.AspNetCore.Identity;
using Chafi.Models;

namespace Chafi.Data
{
    public static class DbInitializer
    {
        public static void Seed(IApplicationBuilder applicationBuilder)
        {
            using (var serviceScope = applicationBuilder.ApplicationServices.CreateScope())
            {
                var context = serviceScope.ServiceProvider.GetService<ChafiDbContext>();
                var userManager = serviceScope.ServiceProvider.GetService<UserManager<ApplicationUser>>();
                var roleManager = serviceScope.ServiceProvider.GetService<RoleManager<IdentityRole>>();

                if (context == null || userManager == null || roleManager == null) return;

                context.Database.EnsureCreated();

                // 1. زرع الصلاحيات وحساب مدير النظام
                SeedRolesAndAdminUserAsync(roleManager, userManager).GetAwaiter().GetResult();

                // 2. زرع البيانات المبدئية للموقع (أقسام، منتجات، إعلانات، وغيرها)
                if (!context.Categories.Any())
                {
                    // --- الأقسام ---
                    var catTreatment = new Category { Name = "التداوي والعلاج", ShortDescription = "منتجات طبيعية مقروء عليها للرقية", IconClass = "fas fa-leaf", ColorHex = "#1a582a", IsActive = true, ShowInMainMenu = true, Slug = "treatment" };
                    var catAudios = new Category { Name = "الصوتيات المتخصصة", ShortDescription = "مقاطع صوتية للرقية الشرعية", IconClass = "fas fa-headphones", ColorHex = "#c8a020", IsActive = true, ShowInMainMenu = true, Slug = "audios" };
                    var catVideos = new Category { Name = "المرئيات والدروس", ShortDescription = "دروس وفيديوهات توعوية عن الرقية", IconClass = "fas fa-video", ColorHex = "#3f834d", IsActive = true, ShowInMainMenu = true, Slug = "videos" };
                    var catBooks = new Category { Name = "المكتبة الرقمية", ShortDescription = "كتب وملفات PDF نافعة", IconClass = "fas fa-book", ColorHex = "#5a463c", IsActive = true, ShowInMainMenu = true, Slug = "books" };

                    context.Categories.AddRange(catTreatment, catAudios, catVideos, catBooks);
                    context.SaveChanges();

                    // --- المنتجات ---
                    var products = new[]
                    {
                        new Product { Name = "عسل سدر يمني للرقية", ShortDescription = "عسل سدر طبيعي أصلي مقروء عليه", Description = "عسل سدر طبيعي 100% مقروء عليه آيات الرقية الشرعية، مفيد في حالات التداوي.", Price = 250m, OldPrice = 300m, StockQuantity = 50, CategoryId = catTreatment.Id, IsActive = true, IsFeatured = true, ImageUrl = "https://picsum.photos/seed/honey/800/800", Slug = "ruqyah-honey" },
                        new Product { Name = "زيت زيتون بكر مقروء", ShortDescription = "زيت زيتون فلسطيني بكر ممتاز", Description = "زيت زيتون عصرة أولى مقروء عليه آيات الشفاء.", Price = 85m, StockQuantity = 100, CategoryId = catTreatment.Id, IsActive = true, IsFeatured = true, ImageUrl = "https://picsum.photos/seed/olive/800/800", Slug = "olive-oil" },
                        new Product { Name = "ماء زمزم مبارك", ShortDescription = "عبوة ماء زمزم مقروء عليها 5 لتر", Description = "ماء زمزم نقي ومقروء عليه الرقية الشرعية كاملة.", Price = 45m, StockQuantity = 200, CategoryId = catTreatment.Id, IsActive = true, IsFeatured = false, ImageUrl = "https://picsum.photos/seed/water/800/800", Slug = "zamzam-water" },
                        new Product { Name = "مسك أسود أصلي", ShortDescription = "مسك أسود سائل للاستخدام الخارجي", Description = "مسك أسود طاهر يستخدم كوقاية وعلاج قبل النوم.", Price = 120m, OldPrice = 150m, StockQuantity = 30, CategoryId = catTreatment.Id, IsActive = true, IsFeatured = true, ImageUrl = "https://picsum.photos/seed/musk/800/800", Slug = "black-musk" }
                    };
                    context.Products.AddRange(products);

                    // --- الصوتيات ---
                    var audios = new[]
                    {
                        new Audio { Title = "الرقية الشرعية الشاملة", Narrator = "الشيخ فلان الفلاني", DurationSeconds = 3600, Price = 0, CategoryId = catAudios.Id, IsActive = true, AudioUrl = "#", ThumbnailUrl = "https://picsum.photos/seed/audio1/400/400" },
                        new Audio { Title = "رقية السحر والمس", Narrator = "الشيخ فلان الفلاني", DurationSeconds = 2400, Price = 50m, CategoryId = catAudios.Id, IsActive = true, AudioUrl = "#", ThumbnailUrl = "https://picsum.photos/seed/audio2/400/400" }
                    };
                    context.Audios.AddRange(audios);

                    // --- الفيديوهات ---
                    var videos = new[]
                    {
                        new Video { Title = "كيف تحصن بيتك وأهلك؟", Presenter = "د. فلان", DurationSeconds = 1200, Price = 0, CategoryId = catVideos.Id, IsActive = true, VideoUrl = "#", ThumbnailUrl = "https://picsum.photos/seed/vid1/600/340" },
                        new Video { Title = "علامات الشفاء من السحر", Presenter = "الشيخ فلان", DurationSeconds = 1800, Price = 100m, CategoryId = catVideos.Id, IsActive = true, VideoUrl = "#", ThumbnailUrl = "https://picsum.photos/seed/vid2/600/340" }
                    };
                    context.Videos.AddRange(videos);

                    // --- الباقات ---
                    context.Packages.Add(new Package
                    {
                        Name = "باقة الشفاء المتكاملة",
                        Description = "تحتوي هذه الباقة على العسل وزيت الزيتون والمسك بخصم خاص.",
                        OriginalTotalPrice = 455m,
                        PackagePrice = 399m,
                        IsActive = true,
                        IsFeatured = true,
                        ImageUrl = "https://picsum.photos/seed/package/800/800"
                    });

                    // --- الإعلانات (الهيرو سلايدر) ---
                    context.Advertisements.AddRange(
                        new Advertisement { Title = "نقاء الجسد والروح\nمع منتجات تشافي", Subtitle = "أفضل المنتجات الطبيعية", Position = AdPosition.HeroSlider, StartDate = DateTime.UtcNow, EndDate = DateTime.UtcNow.AddYears(1), IsActive = true, ImageUrl = "https://picsum.photos/seed/slider1/1920/600", LinkUrl = "/Product" },
                        new Advertisement { Title = "خصم 20% على الباقات\nلفترة محدودة", Subtitle = "عروض حصرية", Position = AdPosition.HeroSlider, StartDate = DateTime.UtcNow, EndDate = DateTime.UtcNow.AddYears(1), IsActive = true, ImageUrl = "https://picsum.photos/seed/slider2/1920/600", LinkUrl = "/Package" }
                    );

                    context.SaveChanges();
                }
            }
        }

        private static async Task SeedRolesAndAdminUserAsync(RoleManager<IdentityRole> roleManager, UserManager<ApplicationUser> userManager)
        {
            string[] roles = { "Admin", "SuperAdmin", "User" };
            foreach (var role in roles)
            {
                if (!await roleManager.RoleExistsAsync(role))
                    await roleManager.CreateAsync(new IdentityRole(role));
            }

            string adminEmail = "admin@tashafi.net";
            if (await userManager.FindByEmailAsync(adminEmail) == null)
            {
                var adminUser = new ApplicationUser { UserName = adminEmail, Email = adminEmail, FullName = "مدير النظام", PhoneNumber = "0500000000", EmailConfirmed = true, IsActive = true };
                var result = await userManager.CreateAsync(adminUser, "Tashafi@2026");
                if (result.Succeeded)
                {
                    await userManager.AddToRoleAsync(adminUser, "Admin");
                    await userManager.AddToRoleAsync(adminUser, "SuperAdmin");
                }
            }
        }
    }
}