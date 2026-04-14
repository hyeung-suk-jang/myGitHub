import { useState, useEffect } from 'react';

interface ScrollState {
  scrollX:       number;
  scrollY:       number;
  isScrolled:    boolean;
  scrollDirection: 'up' | 'down' | null;
}

export function useScroll(threshold: number = 10): ScrollState {
  const [scrollState, setScrollState] = useState<ScrollState>({
    scrollX:         0,
    scrollY:         0,
    isScrolled:      false,
    scrollDirection: null,
  });

  useEffect(() => {
    let lastScrollY = window.scrollY;

    const handleScroll = () => {
      const scrollY    = window.scrollY;
      const scrollX    = window.scrollX;
      const isScrolled = scrollY > threshold;
      const scrollDirection = scrollY > lastScrollY ? 'down' : 'up';

      setScrollState({ scrollX, scrollY, isScrolled, scrollDirection });
      lastScrollY = scrollY;
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, [threshold]);

  return scrollState;
}
