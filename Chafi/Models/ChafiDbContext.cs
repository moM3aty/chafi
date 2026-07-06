// مسار الملف: Models/ChafiDbContext.cs

using Microsoft.AspNetCore.Identity.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore;

namespace Chafi.Models
{
    /// <summary>
    /// سياق قاعدة البيانات الرئيسي - تشافي
    /// </summary>
    public class ChafiDbContext : IdentityDbContext<ApplicationUser>
    {
        public ChafiDbContext(DbContextOptions<ChafiDbContext> options) : base(options)
        {
        }

        public DbSet<Category> Categories { get; set; }
        public DbSet<Product> Products { get; set; }
        public DbSet<ProductImage> ProductImages { get; set; }
        public DbSet<Audio> Audios { get; set; }
        public DbSet<Video> Videos { get; set; }
        public DbSet<UserAudioProgress> UserAudioProgresses { get; set; }
        public DbSet<UserVideoProgress> UserVideoProgresses { get; set; }
        public DbSet<Offer> Offers { get; set; }
        public DbSet<ProductOffer> ProductOffers { get; set; }
        public DbSet<Package> Packages { get; set; }
        public DbSet<PackageItem> PackageItems { get; set; }
        public DbSet<Advertisement> Advertisements { get; set; }
        public DbSet<Cart> Carts { get; set; }
        public DbSet<CartItem> CartItems { get; set; }
        public DbSet<Order> Orders { get; set; }
        public DbSet<OrderItem> OrderItems { get; set; }
        public DbSet<OrderStatusHistory> OrderStatusHistories { get; set; }
        public DbSet<Payment> Payments { get; set; }
        public DbSet<Review> Reviews { get; set; }
        public DbSet<WishlistItem> WishlistItems { get; set; }
        public DbSet<ContactMessage> ContactMessages { get; set; }
        public DbSet<SiteSetting> SiteSettings { get; set; }
        public DbSet<NewsletterSubscription> NewsletterSubscriptions { get; set; }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            base.OnModelCreating(modelBuilder);

            modelBuilder.Entity<Category>()
                .HasOne(c => c.Parent)
                .WithMany(c => c.Children)
                .HasForeignKey(c => c.ParentId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<Category>().HasIndex(c => c.Slug).IsUnique();
            modelBuilder.Entity<Category>().HasIndex(c => c.ParentId);

            modelBuilder.Entity<Product>()
                .HasOne(p => p.Category)
                .WithMany(c => c.Products)
                .HasForeignKey(p => p.CategoryId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<Product>().HasIndex(p => p.Slug).IsUnique();
            modelBuilder.Entity<Product>().HasIndex(p => p.CategoryId);
            modelBuilder.Entity<Product>().HasIndex(p => p.IsFeatured);
            modelBuilder.Entity<Product>().HasIndex(p => p.Price);

            modelBuilder.Entity<ProductImage>()
                .HasOne(pi => pi.Product)
                .WithMany(p => p.Images)
                .HasForeignKey(pi => pi.ProductId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<Audio>()
                .HasOne(a => a.Category)
                .WithMany(c => c.Audios)
                .HasForeignKey(a => a.CategoryId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<Audio>().HasIndex(a => a.CategoryId);

            modelBuilder.Entity<UserAudioProgress>()
                .HasIndex(uap => new { uap.UserId, uap.AudioId }).IsUnique();

            modelBuilder.Entity<Video>()
                .HasOne(v => v.Category)
                .WithMany(c => c.Videos)
                .HasForeignKey(v => v.CategoryId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<Video>().HasIndex(v => v.CategoryId);

            modelBuilder.Entity<UserVideoProgress>()
                .HasIndex(uvp => new { uvp.UserId, uvp.VideoId }).IsUnique();

            modelBuilder.Entity<ProductOffer>().HasKey(po => new { po.ProductId, po.OfferId });

            modelBuilder.Entity<ProductOffer>()
                .HasOne(po => po.Product)
                .WithMany(p => p.ProductOffers)
                .HasForeignKey(po => po.ProductId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<ProductOffer>()
                .HasOne(po => po.Offer)
                .WithMany(o => o.ProductOffers)
                .HasForeignKey(po => po.OfferId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<PackageItem>()
                .HasOne(pi => pi.Package)
                .WithMany(p => p.Items)
                .HasForeignKey(pi => pi.PackageId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<PackageItem>()
                .HasOne(pi => pi.Product)
                .WithMany(p => p.PackageItems)
                .HasForeignKey(pi => pi.ProductId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<Advertisement>().HasIndex(a => a.Position);
            modelBuilder.Entity<Advertisement>().HasIndex(a => a.IsActive);

            modelBuilder.Entity<Cart>()
                .HasOne(c => c.User)
                .WithOne(u => u.Cart)
                .HasForeignKey<Cart>(c => c.UserId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<CartItem>()
                .HasOne(ci => ci.Cart)
                .WithMany(c => c.Items)
                .HasForeignKey(ci => ci.CartUserId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<CartItem>()
                .HasOne(ci => ci.Product)
                .WithMany()
                .HasForeignKey(ci => ci.ProductId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<CartItem>()
                .HasOne(ci => ci.Package)
                .WithMany()
                .HasForeignKey(ci => ci.PackageId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<Order>()
                .HasOne(o => o.User)
                .WithMany(u => u.Orders)
                .HasForeignKey(o => o.UserId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<Order>()
                .HasOne(o => o.Offer)
                .WithMany(o => o.Orders)
                .HasForeignKey(o => o.OfferId)
                .OnDelete(DeleteBehavior.SetNull);

            modelBuilder.Entity<Order>().HasIndex(o => o.OrderNumber).IsUnique();
            modelBuilder.Entity<Order>().HasIndex(o => o.UserId);
            modelBuilder.Entity<Order>().HasIndex(o => o.Status);

            modelBuilder.Entity<OrderItem>()
                .HasOne(oi => oi.Order)
                .WithMany(o => o.Items)
                .HasForeignKey(oi => oi.OrderId)
                .OnDelete(DeleteBehavior.Cascade);

            // تم التحديث هنا لربط العلاقة صراحة بالـ OrderItems داخل جدول المنتجات لحل تعارض ProductId1
            modelBuilder.Entity<OrderItem>()
                .HasOne(oi => oi.Product)
                .WithMany(p => p.OrderItems)
                .HasForeignKey(oi => oi.ProductId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<OrderItem>()
                .HasOne(oi => oi.Package)
                .WithMany(p => p.OrderItems)
                .HasForeignKey(oi => oi.PackageId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<OrderStatusHistory>()
                .HasOne(osh => osh.Order)
                .WithMany(o => o.StatusHistory)
                .HasForeignKey(osh => osh.OrderId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<Payment>()
                .HasOne(p => p.Order)
                .WithOne(o => o.Payment)
                .HasForeignKey<Payment>(p => p.OrderId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<Review>()
                .HasOne(r => r.User)
                .WithMany(u => u.Reviews)
                .HasForeignKey(r => r.UserId)
                .OnDelete(DeleteBehavior.Restrict);

            modelBuilder.Entity<Review>()
                .HasOne(r => r.Product)
                .WithMany(p => p.Reviews)
                .HasForeignKey(r => r.ProductId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<Review>().HasIndex(r => new { r.UserId, r.ProductId }).IsUnique();

            modelBuilder.Entity<WishlistItem>()
                .HasOne(w => w.User)
                .WithMany(u => u.WishlistItems)
                .HasForeignKey(w => w.UserId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<WishlistItem>()
                .HasOne(w => w.Product)
                .WithMany(p => p.WishlistItems)
                .HasForeignKey(w => w.ProductId)
                .OnDelete(DeleteBehavior.Cascade);

            modelBuilder.Entity<WishlistItem>().HasIndex(w => new { w.UserId, w.ProductId }).IsUnique();

            modelBuilder.Entity<NewsletterSubscription>().HasIndex(n => n.Email).IsUnique();
        }
    }
}