import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

/**
 * Get the preferred identifier for a user (username if available, otherwise ID)
 */
export function getPreferredIdentifier(user: { username?: string; id: number }): string {
  return user.username || user.id.toString();
}

/**
 * Get the profile URL for a user
 */
export function getProfileUrl(user: { username?: string; id: number }): string {
  const identifier = getPreferredIdentifier(user);
  return `/profile/${identifier}`;
}
