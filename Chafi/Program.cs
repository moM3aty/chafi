// مسار الملف: Program.cs

using System;
using Microsoft.AspNetCore.Builder;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Configuration;
using Microsoft.AspNetCore.Identity;
using Chafi.Models;
using Chafi.Data;

var builder = WebApplication.CreateBuilder(args);

// إضافة خدمات MVC (Controllers with Views)
builder.Services.AddControllersWithViews();

// إعداد قاعدة البيانات (هنا نستخدم InMemory للتجربة السريعة، ويمكنك تغييرها لـ SqlServer لاحقاً)
builder.Services.AddDbContext<ChafiDbContext>(options =>
    options.UseSqlServer(builder.Configuration.GetConnectionString("DefaultConnection")));

// إعداد نظام الهوية (Identity) للمستخدمين
builder.Services.AddIdentity<ApplicationUser, IdentityRole>(options =>
{
    options.Password.RequireDigit = false;
    options.Password.RequiredLength = 6;
    options.Password.RequireNonAlphanumeric = false;
    options.Password.RequireUppercase = false;
    options.Password.RequireLowercase = false;
})
.AddEntityFrameworkStores<ChafiDbContext>()
.AddDefaultTokenProviders();

var app = builder.Build();

// تشغيل الـ Seeding لملء قاعدة البيانات بالنصوص والأقسام التي أرسلتها (من ملف DbInitializer)
DbInitializer.Seed(app);

if (!app.Environment.IsDevelopment())
{
    app.UseExceptionHandler("/Home/Error");
    app.UseHsts();
}

app.UseHttpsRedirection();
app.UseStaticFiles();

app.UseRouting();

app.UseAuthentication();
app.UseAuthorization();

app.MapControllerRoute(
    name: "default",
    pattern: "{controller=Home}/{action=Index}/{id?}/{slug?}");

app.Run();