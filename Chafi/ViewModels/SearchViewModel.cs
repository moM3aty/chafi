// مسار الملف: ViewModels/SearchViewModel.cs

using System.Collections.Generic;
using Chafi.Models;

namespace Chafi.ViewModels
{
    public class SearchViewModel
    {
        public string Query { get; set; }
        public List<Product> Products { get; set; } = new List<Product>();
        public List<Audio> Audios { get; set; } = new List<Audio>();
        public List<Video> Videos { get; set; } = new List<Video>();

        public int TotalResults => Products.Count + Audios.Count + Videos.Count;
    }
}