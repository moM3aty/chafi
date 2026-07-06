using System.Collections.Generic;
using Chafi.Models;

namespace Chafi.ViewModels
{
    // هذا الموديل مخصص لجمع البيانات المطلوبة وعرضها في الصفحة الرئيسية
    public class HomeViewModel
    {
        public IEnumerable<Advertisement> HeroSliders { get; set; } = new List<Advertisement>();
        public IEnumerable<Category> MainCategories { get; set; } = new List<Category>();
        public IEnumerable<Product> FeaturedProducts { get; set; } = new List<Product>();
        public IEnumerable<Audio> LatestAudios { get; set; } = new List<Audio>();
        public IEnumerable<Video> LatestVideos { get; set; } = new List<Video>();
        public IEnumerable<Package> FeaturedPackages { get; set; } = new List<Package>();
    }
}